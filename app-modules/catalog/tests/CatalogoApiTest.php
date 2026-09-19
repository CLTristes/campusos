<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Models\Course;
use CampusOs\Tenancy\Models\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->entity = tenantContext();
    Campus::factory()->create(['cps_code' => 'FB', 'cps_name' => 'Francisco Beltrão']);
    $this->seed(MatrizUtfprSeeder::class);
    $this->headers = ['X-Tenant-Id' => $this->entity->ent_id];
});

it('lista os cursos da instituição', function () {
    $this->getJson('/api/v1/courses', $this->headers)
        ->assertOk()
        ->assertJsonPath('data.0.code', '25')
        ->assertJsonPath('data.0.degree', 'bachelor')
        ->assertJsonPath('data.0.campus.code', 'FB');
});

it('devolve a matriz com o total calculado pela fórmula do documento', function () {
    $course = Course::query()->where('crs_code', '25')->firstOrFail();

    $this->getJson("/api/v1/courses/{$course->crs_id}/curriculum", $this->headers)
        ->assertOk()
        ->assertJsonPath('data.code', '45')
        ->assertJsonPath('data.workload.mandatory_hours', 2730)
        ->assertJsonPath('data.workload.elective_hours', 210)
        ->assertJsonPath('data.workload.standalone_extension_hours', 60)
        ->assertJsonPath('data.workload.total_hours', 3000)
        ->assertJsonPath('data.workload.extension_hours', 300)
        // a distinção que o produto inteiro depende de acertar
        ->assertJsonPath('data.workload.extension_counts_in_total', false);
});

it('expõe o teto por categoria de atividade complementar (B7), sem entrar no workload', function () {
    $this->seed(CampusOs\Catalog\Database\Seeders\ComplementaryCategorySeeder::class);
    $course = Course::query()->where('crs_code', '25')->firstOrFail();

    $r = $this->getJson("/api/v1/courses/{$course->crs_id}/curriculum", $this->headers)->assertOk();

    expect($r->json('data.complementary_categories'))->toHaveCount(6)
        ->and(collect($r->json('data.complementary_categories'))->firstWhere('name', 'Participação em eventos')['max_hours'])->toBe(40)
        ->and($r->json('data.workload'))->not->toHaveKey('complementary_hours');
});

it('agrupa as disciplinas por período, com os pré-requisitos de cada uma', function () {
    $course = Course::query()->where('crs_code', '25')->firstOrFail();

    $r = $this->getJson("/api/v1/courses/{$course->crs_id}/curriculum", $this->headers)->assertOk();

    $terms = collect($r->json('data.terms'));
    expect($terms->pluck('term')->all())->toBe([1, 2, 3, 4, 5, 6, 7, 8]);

    $p5 = $terms->firstWhere('term', 5);
    $est501 = collect($p5['subjects'])->firstWhere('code', 'EST501');

    // O estágio exige PERÍODO mínimo, não disciplina — e o JSON precisa dizer isso.
    expect($est501['prerequisites'][0]['type'])->toBe('minimum_term')
        ->and($est501['prerequisites'][0]['min_term'])->toBe(5)
        ->and($est501['prerequisites'][0]['subject'])->toBeNull();

    $wbe501 = collect($p5['subjects'])->firstWhere('code', 'WBE501');
    expect(collect($wbe501['prerequisites'])->pluck('subject')->sort()->values()->all())
        ->toBe(['POO303', 'WFE402']);
});

it('expõe o conjunto de optativas com a CHS por período', function () {
    $course = Course::query()->where('crs_code', '25')->firstOrFail();

    $this->getJson("/api/v1/courses/{$course->crs_id}/curriculum", $this->headers)
        ->assertOk()
        ->assertJsonPath('data.elective_groups.0.code', '941')
        ->assertJsonPath('data.elective_groups.0.required_hours', 210)
        ->assertJsonPath('data.elective_groups.0.weekly_hours_per_term', 3.5);
});

it('sem tenant na borda, a leitura do catálogo é recusada', function () {
    $this->getJson('/api/v1/courses')->assertStatus(401);
});

it('o catálogo de outra instituição nunca vaza', function () {
    $outra = tenantContext();

    $this->getJson('/api/v1/courses', ['X-Tenant-Id' => $outra->ent_id])
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('a documentação interativa da API responde', function () {
    $this->get('/docs/api')->assertOk()->assertSee('CampusOS', false);
});

it('a spec OpenAPI é servida e lista os endpoints do catálogo', function () {
    // response()->file() devolve BinaryFileResponse, cujo getContent() é `false`.
    // Testa-se então as duas coisas separadamente e honestamente: a rota serve o
    // arquivo com o content-type certo, e o arquivo tem o conteúdo esperado.
    $this->get('/docs/api/openapi.yaml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/yaml; charset=utf-8');

    $spec = file_get_contents(storage_path('app/private/scribe/openapi.yaml'));

    expect($spec)
        ->toContain('/api/v1/courses')
        // O Scribe expande {course} pela PK do model — a spec documenta
        // {course_crs_id}, que é mais honesto com quem consome a API.
        ->toContain('/api/v1/courses/{course_crs_id}/curriculum')
        ->toContain('CampusOS')
        ->toContain('Catálogo acadêmico');
});

it('a coleção Postman é servida', function () {
    $this->get('/docs/api/postman.json')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json; charset=utf-8');
});
