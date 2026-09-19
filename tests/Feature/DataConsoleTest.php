<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Prerequisite;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Models\EnrollmentRequest;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Lifeos\Models\Task;
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

    // Uma varredura rasa, mas que percorre os grupos de navegação e exercita
    // Resource + Table de cada model do catálogo, tenancy e jornada.
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
        '/data-console/students',
        '/data-console/registrations',
        '/data-console/subject-enrollments',
        '/data-console/enrollment-requests',
        '/data-console/complementary-categories',
        '/data-console/prerequisites',
        '/data-console/complementary-activities',
        '/data-console/notes',
        '/data-console/tasks',
        '/data-console/audit-logs',
    ] as $url) {
        $this->actingAs($coord)->get($url)->assertSuccessful();
    }
});

it('as telas de edição da jornada abrem sem erro, com dado real', function () {
    $this->seed(MatrizUtfprSeeder::class);
    $coord = User::factory()->coordinator()->create();

    $student = Student::factory()->create();
    $registration = Registration::factory()->create([
        'student_std_id' => $student->std_id,
        'course_crs_id' => Course::query()->where('crs_code', '25')->value('crs_id'),
        'curriculum_cur_id' => Curriculum::query()->where('cur_code', '45')->value('cur_id'),
        'entry_term_trm_id' => Term::query()->where('trm_year', 2023)->where('trm_period', 1)->value('trm_id'),
    ]);
    $enrollment = SubjectEnrollment::factory()->create([
        'registration_reg_id' => $registration->reg_id,
        'subject_sbj_id' => Subject::query()->value('sbj_id'),
        'term_trm_id' => Term::query()->value('trm_id'),
    ]);
    // erq_extraction preenchido é o caso que mais importa: o form usa
    // afterStateHydrated/dehydrateStateUsing pra não corromper o JSON.
    $document = EnrollmentRequest::factory()->create([
        'user_usr_id' => User::factory()->create()->usr_id,
        'erq_status' => DocumentRequestStatus::Parsed,
        'erq_extraction' => ['kind' => 'transcript', 'lines' => [['code' => 'ARC102']]],
    ]);

    foreach ([
        "/data-console/students/{$student->std_id}/edit",
        "/data-console/registrations/{$registration->reg_id}/edit",
        "/data-console/subject-enrollments/{$enrollment->sen_id}/edit",
        "/data-console/enrollment-requests/{$document->erq_id}/edit",
    ] as $url) {
        $this->actingAs($coord)->get($url)->assertSuccessful();
    }
});

it('as telas de edição do acervo, tarefas e pré-requisitos abrem sem erro, com dado relacional real', function () {
    $this->seed(MatrizUtfprSeeder::class);
    $coord = User::factory()->coordinator()->create();
    $author = User::factory()->create();

    // Note: FK simples (author, subject) — prova que o NoteVisibilityScope
    // foi removido do Resource (senão dependeria de quem está logado).
    $note = Note::factory()->create([
        'author_usr_id' => $author->usr_id,
        'subject_sbj_id' => Subject::query()->value('sbj_id'),
    ]);

    // Task: auto-relação (origin_tsk_id -> tasks.tsk_id).
    $originTask = Task::factory()->create(['owner_usr_id' => $author->usr_id]);
    $adoptedTask = Task::factory()->create([
        'owner_usr_id' => $coord->usr_id,
        'origin_tsk_id' => $originTask->tsk_id,
    ]);

    // Prerequisite: DUAS BelongsTo pro mesmo CurriculumSubject, dois níveis
    // de dot-notation (curriculumSubject.subject / required.subject).
    $curriculumSubjects = CurriculumSubject::query()->with('subject')->take(2)->get();
    $prerequisite = Prerequisite::factory()->create([
        'curriculum_subject_cbs_id' => $curriculumSubjects[0]->cbs_id,
        'required_cbs_id' => $curriculumSubjects[1]->cbs_id,
    ]);

    foreach ([
        "/data-console/notes/{$note->nte_id}/edit",
        "/data-console/tasks/{$adoptedTask->tsk_id}/edit",
        "/data-console/prerequisites/{$prerequisite->prq_id}/edit",
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
