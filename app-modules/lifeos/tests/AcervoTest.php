<?php

declare(strict_types=1);

use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\Offering;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->entity = tenantContext();
});

/**
 * Matricula um novo aluno (User+Student+Registration+SubjectEnrollment) numa
 * disciplina, num termo "P/AAAA" (ex.: "1/2024"). É o fixture que a Fase 2 do
 * desenho pede: veterano e calouro só diferem no termo em que cursaram.
 *
 * @return array{user: User, registration: Registration, course: Course}
 */
function enrolledStudent(Subject $subject, string $termLabel, ?Course $course = null, ?Offering $offering = null): array
{
    [$period, $year] = array_map('intval', explode('/', $termLabel));
    $term = Term::query()->firstOrCreate(['trm_year' => $year, 'trm_period' => $period]);

    $course ??= Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $curriculum = Curriculum::factory()->create(['course_crs_id' => $course->crs_id]);

    $user = User::factory()->create();
    $student = Student::factory()->create(['user_usr_id' => $user->usr_id]);
    $registration = Registration::factory()->create([
        'student_std_id' => $student->std_id,
        'course_crs_id' => $course->crs_id,
        'curriculum_cur_id' => $curriculum->cur_id,
        'entry_term_trm_id' => $term->trm_id,
    ]);

    SubjectEnrollment::factory()->create([
        'registration_reg_id' => $registration->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $term->trm_id,
        'offering_ofr_id' => $offering?->ofr_id,
    ]);

    return ['user' => $user, 'registration' => $registration, 'course' => $course];
}

it('entrega a nota do veterano de 2024/1 ao calouro de 2027/1 — o desafio 5.2', function () {
    $subject = Subject::factory()->create();
    $veterano = enrolledStudent($subject, '1/2024');
    $calouro = enrolledStudent($subject, '1/2027');

    $nota = Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Subject,
    ]);

    expect(Note::visibleTo($calouro['user'])->pluck('nte_id'))->toContain($nota->nte_id);
});

it('nota privada não atravessa nem a disciplina — só o autor enxerga', function () {
    $subject = Subject::factory()->create();
    $veterano = enrolledStudent($subject, '1/2024');
    $calouro = enrolledStudent($subject, '1/2027');

    $nota = Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Private,
    ]);

    expect(Note::visibleTo($veterano['user'])->pluck('nte_id'))->toContain($nota->nte_id)
        ->and(Note::visibleTo($calouro['user'])->pluck('nte_id'))->not->toContain($nota->nte_id);
});

it('visibilidade offering só entrega pra quem cursou AQUELA turma, não a disciplina inteira', function () {
    $subject = Subject::factory()->create();
    $campus = Campus::factory()->create();
    $termo = Term::query()->firstOrCreate(['trm_year' => 2026, 'trm_period' => 1]);
    $turmaA = Offering::factory()->create(['subject_sbj_id' => $subject->sbj_id, 'term_trm_id' => $termo->trm_id, 'campus_cps_id' => $campus->cps_id, 'ofr_class_code' => 'TURMA-A']);
    $turmaB = Offering::factory()->create(['subject_sbj_id' => $subject->sbj_id, 'term_trm_id' => $termo->trm_id, 'campus_cps_id' => $campus->cps_id, 'ofr_class_code' => 'TURMA-B']);

    $colegaDaTurma = enrolledStudent($subject, '1/2026', offering: $turmaA);
    $outraTurma = enrolledStudent($subject, '1/2026', offering: $turmaB);

    $nota = Note::factory()->create([
        'author_usr_id' => $colegaDaTurma['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'offering_ofr_id' => $turmaA->ofr_id,
        'nte_visibility' => Visibility::Offering,
    ]);

    expect(Note::visibleTo($colegaDaTurma['user'])->pluck('nte_id'))->toContain($nota->nte_id)
        ->and(Note::visibleTo($outraTurma['user'])->pluck('nte_id'))->not->toContain($nota->nte_id);
});

it('visibilidade course entrega pra quem tem vínculo no curso, mesmo sem cursar a disciplina', function () {
    $curso = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $subject = Subject::factory()->create();
    $colegaDeCurso = enrolledStudent(Subject::factory()->create(), '1/2026', course: $curso);
    $autor = enrolledStudent($subject, '1/2025', course: $curso);
    $outroCurso = enrolledStudent($subject, '1/2025');

    $nota = Note::factory()->create([
        'author_usr_id' => $autor['user']->usr_id,
        'course_crs_id' => $curso->crs_id,
        'nte_visibility' => Visibility::Course,
    ]);

    expect(Note::visibleTo($colegaDeCurso['user'])->pluck('nte_id'))->toContain($nota->nte_id)
        ->and(Note::visibleTo($outroCurso['user'])->pluck('nte_id'))->not->toContain($nota->nte_id);
});

it('visibilidade institution entrega pra qualquer um da mesma instituição, nunca de outra', function () {
    $subject = Subject::factory()->create();
    $autor = enrolledStudent($subject, '1/2025');
    $qualquerUmDaCasa = User::factory()->create();

    $nota = Note::factory()->create([
        'author_usr_id' => $autor['user']->usr_id,
        'nte_visibility' => Visibility::Institution,
    ]);

    $outraEntidade = Entity::factory()->create();
    $outraInstituicao = CampusOs\Core\Tenancy\TenantContext::runAs(
        $outraEntidade->ent_id,
        fn () => User::factory()->create(),
    );

    expect(Note::visibleTo($qualquerUmDaCasa)->pluck('nte_id'))->toContain($nota->nte_id);

    // O EntityScope filtra pelo TenantContext AMBIENTE, não pelo usuário
    // passado a visibleTo() — em produção, o middleware sempre alinha os
    // dois; aqui simulamos essa mesma amarração explicitamente.
    CampusOs\Core\Tenancy\TenantContext::runAs(
        $outraEntidade->ent_id,
        fn () => expect(Note::visibleTo($outraInstituicao)->pluck('nte_id'))->not->toContain($nota->nte_id),
    );
});

it('a nota nasce privada mesmo se o cliente pedir outra visibilidade', function () {
    $user = User::factory()->create();

    $body = $this->actingAs($user)
        ->postJson('/api/v1/notes', [
            'title' => 'Resumo da P2',
            'body_md' => 'Conteúdo.',
            'kind' => 'summary',
            'visibility' => 'institution',
        ])
        ->assertCreated()
        ->json('data');

    expect($body['visibility'])->toBe('private');
});

it('publicar para subject sem disciplina informada é recusado', function () {
    $user = User::factory()->create();

    $note = Note::factory()->create(['author_usr_id' => $user->usr_id]);

    $this->actingAs($user)
        ->postJson("/api/v1/notes/{$note->nte_id}/visibility", ['visibility' => 'subject'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('subject_id');
});

it('publicar e depois despublicar funciona sem pré-condição', function () {
    $subject = Subject::factory()->create();
    $user = User::factory()->create();
    $note = Note::factory()->create(['author_usr_id' => $user->usr_id]);

    $this->actingAs($user)
        ->postJson("/api/v1/notes/{$note->nte_id}/visibility", [
            'visibility' => 'subject',
            'subject_id' => $subject->sbj_id,
        ])
        ->assertOk()
        ->assertJsonPath('data.visibility', 'subject');

    expect($note->refresh()->nte_published_at)->not->toBeNull();

    $this->actingAs($user)
        ->postJson("/api/v1/notes/{$note->nte_id}/visibility", ['visibility' => 'private'])
        ->assertOk()
        ->assertJsonPath('data.visibility', 'private');
});

it('só o autor pode mudar a visibilidade — quem só enxerga recebe 403', function () {
    $subject = Subject::factory()->create();
    $veterano = enrolledStudent($subject, '1/2024');
    $calouro = enrolledStudent($subject, '1/2027');

    $nota = Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Subject,
    ]);

    $this->actingAs($calouro['user'])
        ->postJson("/api/v1/notes/{$nota->nte_id}/visibility", ['visibility' => 'private'])
        ->assertForbidden();
});

it('lista o acervo de uma disciplina filtrando por subject_id', function () {
    $subject = Subject::factory()->create();
    $outraSubject = Subject::factory()->create();
    $veterano = enrolledStudent($subject, '1/2024');
    $calouro = enrolledStudent($subject, '1/2027');

    $daDisciplina = Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Subject,
    ]);
    Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $outraSubject->sbj_id,
        'nte_visibility' => Visibility::Subject,
    ]);

    $this->actingAs($calouro['user'])
        ->getJson("/api/v1/notes?subject_id={$subject->sbj_id}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $daDisciplina->nte_id);
});

it('a autoria aparece na resposta — o acervo anônimo não cria pertencimento', function () {
    $subject = Subject::factory()->create();
    $veterano = enrolledStudent($subject, '1/2024');

    $nota = Note::factory()->create([
        'author_usr_id' => $veterano['user']->usr_id,
        'subject_sbj_id' => $subject->sbj_id,
        'nte_visibility' => Visibility::Subject,
    ]);

    $this->actingAs($veterano['user'])
        ->getJson("/api/v1/notes/{$nota->nte_id}")
        ->assertOk()
        ->assertJsonPath('data.author.name', $veterano['user']->usr_name);
});
