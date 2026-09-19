<?php

declare(strict_types=1);

use App\Mcp\Servers\CampusOsServer;
use App\Mcp\Tools\AcervoDaDisciplinaTool;
use App\Mcp\Tools\BuscarAnotacoesTool;
use App\Mcp\Tools\CriarAnotacaoTool;
use App\Mcp\Tools\DisciplinasLiberadasTool;
use App\Mcp\Tools\MinhaAgendaTool;
use App\Mcp\Tools\MinhaProgressaoTool;
use App\Mcp\Tools\MinhasHorasTool;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Lifeos\Models\Task;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();

    $this->course = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $this->curriculum = Curriculum::factory()->create(['course_crs_id' => $this->course->crs_id]);

    $this->user = User::factory()->create();
    $this->registration = Registration::factory()->create([
        'student_std_id' => Student::factory()->create(['user_usr_id' => $this->user->usr_id])->std_id,
        'course_crs_id' => $this->course->crs_id,
        'curriculum_cur_id' => $this->curriculum->cur_id,
        'entry_term_trm_id' => Term::factory()->create()->trm_id,
    ]);
});

it('minha_progressao devolve a mesma progressão do endpoint REST', function () {
    CampusOsServer::actingAs($this->user)
        ->tool(MinhaProgressaoTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data.overall')
            ->where('data.overall.required_hours', 3000)
            ->etc());
});

it('minha_progressao sem vínculo devolve erro explicativo, não um 500 genérico', function () {
    $semVinculo = User::factory()->create();

    CampusOsServer::actingAs($semVinculo)
        ->tool(MinhaProgressaoTool::class)
        ->assertHasErrors();
});

it('disciplinas_liberadas devolve liberadas e travadas', function () {
    CampusOsServer::actingAs($this->user)
        ->tool(DisciplinasLiberadasTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data.current_period')
            ->has('data.eligible')
            ->has('data.blocked')
            ->etc());
});

it('acervo_da_disciplina respeita NoteVisibilityScope — nota privada de outra pessoa não aparece', function () {
    $subject = Subject::factory()->create();
    $outraPessoa = User::factory()->create();

    $notaAlheia = Note::factory()->create([
        'author_usr_id' => $outraPessoa->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Private,
    ]);
    $notaPublica = Note::factory()->create([
        'author_usr_id' => $outraPessoa->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Institution,
    ]);

    CampusOsServer::actingAs($this->user)
        ->tool(AcervoDaDisciplinaTool::class, ['subject_id' => $subject->sbj_id])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data', 1)
            ->where('data.0.id', $notaPublica->nte_id)
            ->etc());

    expect($notaAlheia)->not->toBeNull(); // só documentando o fixture, a asserção real é o has('data', 1) acima
});

it('acervo_da_disciplina sem subject_id é recusado', function () {
    CampusOsServer::actingAs($this->user)
        ->tool(AcervoDaDisciplinaTool::class)
        ->assertHasErrors();
});

it('buscar_anotacoes encontra pelo título', function () {
    $nota = Note::factory()->create([
        'author_usr_id' => $this->user->usr_id,
        'nte_title' => 'Resumo de séries e integrais impróprias',
        'nte_visibility' => Visibility::Private,
    ]);
    Note::factory()->create([
        'author_usr_id' => $this->user->usr_id,
        'nte_title' => 'Assunto completamente diferente',
        'nte_visibility' => Visibility::Private,
    ]);

    CampusOsServer::actingAs($this->user)
        ->tool(BuscarAnotacoesTool::class, ['query' => 'séries'])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data', 1)
            ->where('data.0.id', $nota->nte_id)
            ->etc());
});

it('minha_agenda devolve a mesma agenda do endpoint REST', function () {
    Task::factory()->create(['owner_usr_id' => $this->user->usr_id, 'tsk_title' => 'Ler capítulo 4']);

    CampusOsServer::actingAs($this->user)
        ->tool(MinhaAgendaTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data', 1)
            ->where('data.0.title', 'Ler capítulo 4')
            ->etc());
});

it('minhas_horas devolve o resumo por categoria', function () {
    CampusOsServer::actingAs($this->user)
        ->tool(MinhasHorasTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json->has('data')->etc());
});

it('criar_anotacao sem a habilidade mcp:write é recusado', function () {
    $tokenSoLeitura = $this->user->createToken('mcp', ['mcp:read']);
    $this->user->withAccessToken($tokenSoLeitura->accessToken);

    CampusOsServer::actingAs($this->user)
        ->tool(CriarAnotacaoTool::class, [
            'title' => 'Tentativa sem escrita',
            'body_md' => 'Conteúdo.',
            'kind' => 'summary',
        ])
        ->assertHasErrors();

    expect(Note::query()->count())->toBe(0);
});

it('criar_anotacao com mcp:write cria a nota, sempre privada', function () {
    $tokenComEscrita = $this->user->createToken('mcp', ['mcp:read', 'mcp:write']);
    $this->user->withAccessToken($tokenComEscrita->accessToken);

    CampusOsServer::actingAs($this->user)
        ->tool(CriarAnotacaoTool::class, [
            'title' => 'Resumo da P2',
            'body_md' => 'Conteúdo.',
            'kind' => 'summary',
            'visibility' => 'institution',
        ])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('data.title', 'Resumo da P2')
            ->where('data.visibility', 'private')
            ->etc());

    expect(Note::query()->sole()->nte_visibility)->toBe(Visibility::Private);
});

it('POST /mcp sem token é recusado antes de qualquer tool rodar', function () {
    $this->postJson('/mcp', [])->assertUnauthorized();
});

it('POST /mcp com token sem mcp:read é recusado', function () {
    $token = $this->user->createToken('outro-uso', ['algo-que-nao-e-mcp']);

    $this->withToken($token->plainTextToken)
        ->postJson('/mcp', [])
        ->assertForbidden();
});
