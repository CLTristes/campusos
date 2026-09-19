<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * O console de dados é ferramenta de dev/QA, não tela do produto — e é
 * justamente por isso que quem entra precisa de teste: um painel sem escopo de
 * curso, sobre dado real, aberto ao papel errado é pior que não ter painel.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->utfpr = tenantContext();
    Campus::factory()->create(['cps_code' => 'FB']);
});

it('a coordenação entra no console', function () {
    $coord = User::factory()->coordinator()->create();

    expect($coord->canAccessPanel(Filament::getPanel('data-console')))->toBeTrue();
});

it('o estudante NUNCA entra no console, mesmo sendo usuário válido da API', function () {
    $aluno = User::factory()->create(['usr_role' => UserRole::Student]);

    expect($aluno->canAccessPanel(Filament::getPanel('data-console')))->toBeFalse();
});

it('o painel RENDERIZA para quem entrou — não basta poder acessar', function () {
    // Este teste existe por causa de um bug real: canAccessPanel() devolvia
    // true, o login passava, e a página seguinte estourava em
    // "FilamentManager::getUserName(): must be of type string, null returned"
    // — porque o model declarava getFilamentName() sem implementar o contrato
    // HasName, e o Filament caía no fallback getAttributeValue('name'), coluna
    // que não existe aqui (é usr_name).
    //
    // Testar o método não pega isso. Só renderizar pega.
    $coord = User::factory()->coordinator()->create();

    $this->actingAs($coord)
        ->get('/data-console')
        ->assertSuccessful()
        ->assertSee($coord->usr_name, false);
});

it('as listagens do console abrem sem erro', function () {
    $this->seed(MatrizUtfprSeeder::class);
    $coord = User::factory()->coordinator()->create();

    // Uma varredura rasa, mas que percorre os dois grupos de navegação e
    // exercita Resource + Table de cada model do catálogo.
    foreach ([
        '/data-console/subjects',
        '/data-console/courses',
        '/data-console/curricula',
        '/data-console/curriculum-subjects',
        '/data-console/elective-groups',
        '/data-console/terms',
        '/data-console/offerings',
        '/data-console/campuses',
        '/data-console/users',
    ] as $url) {
        $this->actingAs($coord)->get($url)->assertSuccessful();
    }
});

it('sem sessão, o console manda para o login', function () {
    $this->get('/data-console')->assertRedirect('/data-console/login');
});

it('a página de login do console responde', function () {
    $this->get('/data-console/login')->assertOk();
});

it('o console enxerga apenas os dados da instituição de quem entrou', function () {
    $this->seed(MatrizUtfprSeeder::class);
    // Sem número mágico: o que importa aqui é o ISOLAMENTO, e a contagem exata
    // da matriz é asserção do MatrizUtfprTest, que é quem tem o gabarito.
    $daUtfpr = Subject::query()->count();
    expect($daUtfpr)->toBeGreaterThan(100);

    // Uma segunda instituição, com o próprio catálogo.
    $outra = Entity::factory()->create();
    TenantContext::runAs($outra->ent_id, function (): void {
        Campus::factory()->create(['cps_code' => 'XX']);
        Subject::factory()->count(3)->create();
    });

    $coord = User::factory()->coordinator()->create(['entity_ent_id' => $this->utfpr->ent_id]);

    // O ResolveTenantFromUser do painel define o contexto a partir do usuário —
    // é o que impede o coordenador de uma instituição de ver a grade da outra.
    TenantContext::runAs($coord->entity_ent_id, function () use ($daUtfpr): void {
        expect(Subject::query()->count())->toBe($daUtfpr);
    });

    TenantContext::runAs($outra->ent_id, function (): void {
        expect(Subject::query()->count())->toBe(3);
    });
});
