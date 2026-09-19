<?php

declare(strict_types=1);

use CampusOs\Catalog\Models\ComplementaryCategory;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Enums\ActivityStatus;
use CampusOs\Journey\Models\ComplementaryActivity;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
    Storage::fake('local');

    $this->course = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $this->curriculum = Curriculum::factory()->create(['course_crs_id' => $this->course->crs_id]);
    $this->eventos = ComplementaryCategory::factory()->create([
        'curriculum_cur_id' => $this->curriculum->cur_id,
        'ccg_name' => 'Participação em eventos',
        'ccg_max_hours' => 40,
    ]);

    $this->user = User::factory()->create();
    $this->registration = Registration::factory()->create([
        'student_std_id' => Student::factory()->create(['user_usr_id' => $this->user->usr_id])->std_id,
        'course_crs_id' => $this->course->crs_id,
        'curriculum_cur_id' => $this->curriculum->cur_id,
        'entry_term_trm_id' => Term::factory()->create()->trm_id,
    ]);
});

it('declarar dentro do teto não avisa nada', function () {
    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/me/complementary-activities', [
            'category_id' => $this->eventos->ccg_id,
            'title' => 'Semana Acadêmica',
            'hours_claimed' => 20,
        ])
        ->assertCreated()
        ->json();

    expect($body['data']['hours_claimed'])->toBe(20)
        ->and($body['data']['status'])->toBe('submitted')
        ->and($body['capped'])->toBeFalse()
        ->and($body['hours_not_counted'])->toBe(0);
});

it('estourar o teto avisa quantas horas não contam — sem dividir entre certificados', function () {
    ComplementaryActivity::factory()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'complementary_category_ccg_id' => $this->eventos->ccg_id,
        'cac_hours_claimed' => 66,
    ]);

    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/me/complementary-activities', [
            'category_id' => $this->eventos->ccg_id,
            'title' => 'Mais um evento',
            'hours_claimed' => 20,
        ])
        ->assertCreated()
        ->json();

    // 66 + 20 = 86 declarado, teto 40 => conta 40, perde 46 (o exemplo do desenho).
    expect($body['capped'])->toBeTrue()
        ->and($body['hours_not_counted'])->toBe(46);
});

it('declarar contra categoria de outra matriz é recusado', function () {
    $outraCurricula = Curriculum::factory()->create(['course_crs_id' => Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id])->crs_id]);
    $categoriaAlheia = ComplementaryCategory::factory()->create(['curriculum_cur_id' => $outraCurricula->cur_id]);

    $this->actingAs($this->user)
        ->postJson('/api/v1/me/complementary-activities', [
            'category_id' => $categoriaAlheia->ccg_id,
            'title' => 'Evento',
            'hours_claimed' => 10,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('category_id');
});

it('certificado com tipo de arquivo inválido é recusado', function () {
    $this->actingAs($this->user)
        ->postJson('/api/v1/me/complementary-activities', [
            'category_id' => $this->eventos->ccg_id,
            'title' => 'Evento',
            'hours_claimed' => 10,
            'certificate' => UploadedFile::fake()->create('certificado.exe', 10),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('certificate');
});

it('guarda o certificado e devolve has_certificate', function () {
    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/me/complementary-activities', [
            'category_id' => $this->eventos->ccg_id,
            'title' => 'Evento com certificado',
            'hours_claimed' => 10,
            'certificate' => UploadedFile::fake()->create('certificado.pdf', 200, 'application/pdf'),
        ])
        ->assertCreated()
        ->json('data');

    expect($body['has_certificate'])->toBeTrue();
    Storage::disk('local')->assertExists(ComplementaryActivity::find($body['id'])->cac_certificate_path);
});

it('sem vínculo acadêmico, a rota devolve 404', function () {
    $semVinculo = User::factory()->create();

    $this->actingAs($semVinculo)
        ->postJson('/api/v1/me/complementary-activities', [
            'category_id' => $this->eventos->ccg_id,
            'title' => 'Evento',
            'hours_claimed' => 10,
        ])
        ->assertNotFound();
});

it('lista as próprias atividades com o resumo por categoria', function () {
    ComplementaryActivity::factory()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'complementary_category_ccg_id' => $this->eventos->ccg_id,
        'cac_hours_claimed' => 30,
    ]);

    $body = $this->actingAs($this->user)
        ->getJson('/api/v1/me/complementary-activities')
        ->assertOk()
        ->json();

    expect($body['data'])->toHaveCount(1)
        ->and($body['summary'][0]['hours_claimed'])->toBe(30)
        ->and($body['summary'][0]['hours_granted'])->toBe(30);
});

it('atividade rejeitada não conta no resumo', function () {
    ComplementaryActivity::factory()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'complementary_category_ccg_id' => $this->eventos->ccg_id,
        'cac_hours_claimed' => 30,
        'cac_status' => ActivityStatus::Rejected,
    ]);

    $body = $this->actingAs($this->user)
        ->getJson('/api/v1/me/complementary-activities')
        ->assertOk()
        ->json();

    expect($body['summary'])->toBeEmpty();
});

it('o caminho do certificado fica fora da trilha de auditoria', function () {
    $activity = ComplementaryActivity::factory()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'complementary_category_ccg_id' => $this->eventos->ccg_id,
        'cac_certificate_path' => 'complementary-certificates/algum-arquivo.pdf',
    ]);
    $activity->update(['cac_hours_claimed' => 25]);

    $trilha = CampusOs\Core\Models\AuditLog::query()
        ->where('aud_table', 'complementary_activities')->get();

    expect($trilha)->not->toBeEmpty();

    foreach ($trilha as $log) {
        expect($log->aud_after ?? [])->not->toHaveKey('cac_certificate_path');
    }
});
