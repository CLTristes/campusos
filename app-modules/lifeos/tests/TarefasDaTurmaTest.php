<?php

declare(strict_types=1);

use CampusOs\Catalog\Enums\TermStatus;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\Offering;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Task;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->entity = tenantContext();
});

/**
 * Matricula um novo aluno (User+Student+Registration+SubjectEnrollment) numa
 * oferta específica — é o fixture que a agenda (B6) precisa: "em quais
 * ofertas o aluno está matriculado" depende da oferta, não só da disciplina.
 *
 * @return array{user: User, registration: Registration}
 */
function matriculadoNaOferta(Offering $offering): array
{
    $course = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $curriculum = Curriculum::factory()->create(['course_crs_id' => $course->crs_id]);

    $user = User::factory()->create();
    $student = Student::factory()->create(['user_usr_id' => $user->usr_id]);
    $registration = Registration::factory()->create([
        'student_std_id' => $student->std_id,
        'course_crs_id' => $course->crs_id,
        'curriculum_cur_id' => $curriculum->cur_id,
        'entry_term_trm_id' => $offering->term_trm_id,
    ]);

    SubjectEnrollment::factory()->create([
        'registration_reg_id' => $registration->reg_id,
        'subject_sbj_id' => $offering->subject_sbj_id,
        'term_trm_id' => $offering->term_trm_id,
        'offering_ofr_id' => $offering->ofr_id,
    ]);

    return ['user' => $user, 'registration' => $registration];
}

function ofertaNoTermo(TermStatus $status): Offering
{
    $campus = Campus::factory()->create();
    $subject = Subject::factory()->create();
    $term = Term::factory()->create(['trm_status' => $status]);

    return Offering::factory()->create([
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $term->trm_id,
        'campus_cps_id' => $campus->cps_id,
    ]);
}

it('cadastrar já na turma dispensa um passo de publicação — ao contrário da nota', function () {
    $oferta = ofertaNoTermo(TermStatus::Current);
    $veterano = matriculadoNaOferta($oferta);

    $body = $this->actingAs($veterano['user'])
        ->postJson('/api/v1/tasks', [
            'title' => 'Prova 2',
            'kind' => 'exam',
            'due_at' => now()->addDays(10)->toIso8601String(),
            'visibility' => 'offering',
            'subject_id' => $oferta->subject_sbj_id,
            'offering_id' => $oferta->ofr_id,
        ])
        ->assertCreated()
        ->json('data');

    expect($body['visibility'])->toBe('offering');
});

it('cadastrar para a turma sem informar a turma é recusado', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/tasks', [
            'title' => 'Prova 2',
            'kind' => 'exam',
            'visibility' => 'offering',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('offering_id');
});

it('adotar copia a tarefa da turma — não afeta a original nem compartilha a linha', function () {
    $oferta = ofertaNoTermo(TermStatus::Current);
    $autor = matriculadoNaOferta($oferta);
    $colega = matriculadoNaOferta($oferta);

    $tarefa = Task::factory()->create([
        'owner_usr_id' => $autor['user']->usr_id,
        'subject_sbj_id' => $oferta->subject_sbj_id,
        'offering_ofr_id' => $oferta->ofr_id,
        'tsk_visibility' => Visibility::Offering,
        'tsk_title' => 'Entrega do trabalho',
    ]);

    $copia = $this->actingAs($colega['user'])
        ->postJson("/api/v1/tasks/{$tarefa->tsk_id}/adopt")
        ->assertOk()
        ->json('data');

    expect($copia['visibility'])->toBe('private')
        ->and($copia['origin_task_id'])->toBe($tarefa->tsk_id)
        ->and($copia['id'])->not->toBe($tarefa->tsk_id);

    $this->actingAs($colega['user'])
        ->patchJson("/api/v1/tasks/{$copia['id']}/status", ['status' => 'done'])
        ->assertOk();

    expect($tarefa->refresh()->tsk_status)->toBe(TaskStatus::Todo);
});

it('duplo clique em adotar não gera duas cópias — guarda de idempotência', function () {
    $oferta = ofertaNoTermo(TermStatus::Current);
    $autor = matriculadoNaOferta($oferta);
    $colega = matriculadoNaOferta($oferta);

    $tarefa = Task::factory()->create([
        'owner_usr_id' => $autor['user']->usr_id,
        'offering_ofr_id' => $oferta->ofr_id,
        'tsk_visibility' => Visibility::Offering,
    ]);

    $primeira = $this->actingAs($colega['user'])
        ->postJson("/api/v1/tasks/{$tarefa->tsk_id}/adopt")
        ->assertOk()
        ->json('data.id');

    $segunda = $this->actingAs($colega['user'])
        ->postJson("/api/v1/tasks/{$tarefa->tsk_id}/adopt")
        ->assertOk()
        ->json('data.id');

    expect($segunda)->toBe($primeira)
        ->and(Task::query()->where('origin_tsk_id', $tarefa->tsk_id)->count())->toBe(1);
});

it('adotar uma tarefa privada de outra pessoa é recusado', function () {
    $dono = User::factory()->create();
    $outro = User::factory()->create();

    $tarefa = Task::factory()->create(['owner_usr_id' => $dono->usr_id]);

    $this->actingAs($outro)
        ->postJson("/api/v1/tasks/{$tarefa->tsk_id}/adopt")
        ->assertForbidden();
});

it('a agenda traz a pendência própria e a tarefa da turma ainda não adotada', function () {
    $oferta = ofertaNoTermo(TermStatus::Current);
    $autor = matriculadoNaOferta($oferta);
    $aluno = matriculadoNaOferta($oferta);

    $propria = Task::factory()->create([
        'owner_usr_id' => $aluno['user']->usr_id,
        'tsk_title' => 'Ler capítulo 4',
        'tsk_due_at' => now()->addDays(3),
    ]);

    $daTurma = Task::factory()->create([
        'owner_usr_id' => $autor['user']->usr_id,
        'offering_ofr_id' => $oferta->ofr_id,
        'tsk_visibility' => Visibility::Offering,
        'tsk_title' => 'Prova 2',
        'tsk_due_at' => now()->addDays(10),
    ]);

    $agenda = $this->actingAs($aluno['user'])
        ->getJson('/api/v1/me/agenda')
        ->assertOk()
        ->json('data');

    $ids = array_column($agenda, 'id');
    $porId = array_column($agenda, 'adopted', 'id');

    expect($ids)->toContain($propria->tsk_id)
        ->and($ids)->toContain($daTurma->tsk_id)
        ->and($porId[$daTurma->tsk_id])->toBeFalse();
});

it('tarefa de oferta de termo passado não aparece na agenda — o filtro por termo existe aqui', function () {
    $ofertaPassada = ofertaNoTermo(TermStatus::Closed);
    $autor = matriculadoNaOferta($ofertaPassada);
    $aluno = matriculadoNaOferta($ofertaPassada);

    $tarefaAntiga = Task::factory()->create([
        'owner_usr_id' => $autor['user']->usr_id,
        'offering_ofr_id' => $ofertaPassada->ofr_id,
        'tsk_visibility' => Visibility::Offering,
    ]);

    $agenda = $this->actingAs($aluno['user'])
        ->getJson('/api/v1/me/agenda')
        ->assertOk()
        ->json('data');

    expect(array_column($agenda, 'id'))->not->toContain($tarefaAntiga->tsk_id);
});

it('depois de adotar, a tarefa da turma sai da lista de compartilhadas da agenda', function () {
    $oferta = ofertaNoTermo(TermStatus::Current);
    $autor = matriculadoNaOferta($oferta);
    $aluno = matriculadoNaOferta($oferta);

    $daTurma = Task::factory()->create([
        'owner_usr_id' => $autor['user']->usr_id,
        'offering_ofr_id' => $oferta->ofr_id,
        'tsk_visibility' => Visibility::Offering,
    ]);

    $this->actingAs($aluno['user'])->postJson("/api/v1/tasks/{$daTurma->tsk_id}/adopt")->assertOk();

    $agenda = $this->actingAs($aluno['user'])
        ->getJson('/api/v1/me/agenda')
        ->assertOk()
        ->json('data');

    $porId = array_column($agenda, 'adopted', 'id');

    expect($porId)->not->toHaveKey($daTurma->tsk_id);
    expect(collect($agenda)->firstWhere('origin_task_id', $daTurma->tsk_id)['adopted'])->toBeTrue();
});

it('marcar como feita tira a tarefa da lista de pendências da agenda', function () {
    $user = User::factory()->create();
    $tarefa = Task::factory()->create(['owner_usr_id' => $user->usr_id]);

    $this->actingAs($user)
        ->patchJson("/api/v1/tasks/{$tarefa->tsk_id}/status", ['status' => 'done'])
        ->assertOk()
        ->assertJsonPath('data.status', 'done');

    $agenda = $this->actingAs($user)->getJson('/api/v1/me/agenda')->json('data');

    expect(array_column($agenda, 'id'))->not->toContain($tarefa->tsk_id);
});

it('só o dono muda o status — quem recebeu a tarefa da turma sem adotar recebe 403', function () {
    $oferta = ofertaNoTermo(TermStatus::Current);
    $autor = matriculadoNaOferta($oferta);
    $colega = matriculadoNaOferta($oferta);

    $tarefa = Task::factory()->create([
        'owner_usr_id' => $autor['user']->usr_id,
        'offering_ofr_id' => $oferta->ofr_id,
        'tsk_visibility' => Visibility::Offering,
    ]);

    $this->actingAs($colega['user'])
        ->patchJson("/api/v1/tasks/{$tarefa->tsk_id}/status", ['status' => 'done'])
        ->assertForbidden();
});
