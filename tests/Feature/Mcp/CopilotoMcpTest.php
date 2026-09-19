<?php

declare(strict_types=1);

use App\Mcp\Servers\CampusOsServer;
use App\Mcp\Tools\AcervoDaDisciplinaTool;
use App\Mcp\Tools\BuscarAnotacoesTool;
use App\Mcp\Tools\CriarAnotacaoTool;
use App\Mcp\Tools\DisciplinasLiberadasTool;
use App\Mcp\Tools\MatrizCurricularTool;
use App\Mcp\Tools\MeuHistoricoTool;
use App\Mcp\Tools\MinhaAgendaTool;
use App\Mcp\Tools\MinhaProgressaoTool;
use App\Mcp\Tools\MinhasAnotacoesTool;
use App\Mcp\Tools\MinhasHorasTool;
use App\Mcp\Tools\SimularReprovacaoTool;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Prerequisite;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
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

it('meu_historico devolve o histórico disciplina por disciplina, inclusive o que ainda está cursando', function () {
    $calc1 = Subject::factory()->create(['sbj_code' => 'CALC1', 'sbj_name' => 'Cálculo 1']);
    $termo1 = Term::factory()->create(['trm_year' => 2023, 'trm_period' => 1]);
    SubjectEnrollment::factory()->approved(60)->create([
        'registration_reg_id' => $this->registration->reg_id,
        'subject_sbj_id' => $calc1->sbj_id,
        'term_trm_id' => $termo1->trm_id,
        'sen_grade' => 8.5,
    ]);

    CampusOsServer::actingAs($this->user)
        ->tool(MeuHistoricoTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data', 1)
            ->where('data.0.subject.code', 'CALC1')
            ->where('data.0.status', 'approved')
            ->where('data.0.grade', 8.5)
            ->where('data.0.counts_as_completed', true)
            ->etc());
});

it('matriz_curricular devolve a estrutura inteira do curso — não só o que falta pro aluno', function () {
    $calc1 = Subject::factory()->create(['sbj_code' => 'CALC1', 'sbj_name' => 'Cálculo 1']);
    CurriculumSubject::factory()->create([
        'curriculum_cur_id' => $this->curriculum->cur_id,
        'subject_sbj_id' => $calc1->sbj_id,
        'cbs_term' => 1,
    ]);

    CampusOsServer::actingAs($this->user)
        ->tool(MatrizCurricularTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('data.id', $this->curriculum->cur_id)
            ->has('data.terms', 1)
            ->where('data.terms.0.subjects.0.code', 'CALC1')
            ->etc());
});

it('simular_reprovacao mostra o impacto em cascata, sem gravar nada no banco', function () {
    $calc1 = Subject::factory()->create(['sbj_code' => 'CALC1']);
    $calc2 = Subject::factory()->create(['sbj_code' => 'CALC2']);
    $csCalc1 = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $calc1->sbj_id, 'cbs_term' => 1]);
    $csCalc2 = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $calc2->sbj_id, 'cbs_term' => 2]);
    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $csCalc2->cbs_id, 'required_cbs_id' => $csCalc1->cbs_id]);

    SubjectEnrollment::factory()->approved()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'subject_sbj_id' => $calc1->sbj_id,
        'term_trm_id' => Term::factory()->create(['trm_year' => 2024, 'trm_period' => 1])->trm_id,
    ]);

    CampusOsServer::actingAs($this->user)
        ->tool(SimularReprovacaoTool::class, ['fail' => ['CALC1']])
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->where('data.failed', ['CALC1'])
            ->has('data.affected_subjects', 1)
            ->where('data.affected_subjects.0.subject.code', 'CALC2')
            ->etc());

    expect(SubjectEnrollment::query()->count())->toBe(1);
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

it('minhas_anotacoes só devolve o que o próprio aluno escreveu, mesmo privado e não publicado', function () {
    $minha = Note::factory()->create([
        'author_usr_id' => $this->user->usr_id,
        'nte_visibility' => Visibility::Private,
    ]);
    $outraPessoa = User::factory()->create();
    Note::factory()->create([
        'author_usr_id' => $outraPessoa->usr_id,
        'nte_visibility' => Visibility::Institution,
    ]);

    CampusOsServer::actingAs($this->user)
        ->tool(MinhasAnotacoesTool::class)
        ->assertOk()
        ->assertStructuredContent(fn (AssertableJson $json) => $json
            ->has('data', 1)
            ->where('data.0.id', $minha->nte_id)
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
