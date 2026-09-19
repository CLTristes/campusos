<?php

declare(strict_types=1);

use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();

    $this->campus = Campus::factory()->create();
    $this->course = Course::factory()->create(['campus_cps_id' => $this->campus->cps_id]);
    $this->curriculum = Curriculum::factory()->create(['course_crs_id' => $this->course->crs_id]);
    $this->termo = Term::factory()->create(['trm_year' => 2023, 'trm_period' => 1]);

    $this->coordenador = User::factory()->create(['usr_role' => UserRole::Coordinator, 'course_crs_id' => $this->course->crs_id]);
    $this->gestao = User::factory()->create(['usr_role' => UserRole::InstitutionAdmin]);
    $this->aluno = User::factory()->create(['usr_role' => UserRole::Student]);
});

/** @return array{user: User, registration: Registration} */
function alunoMatriculado(Course $course, Curriculum $curriculum, Term $entryTerm, string $nome): array
{
    $user = User::factory()->create(['usr_role' => UserRole::Student]);
    $student = Student::factory()->create(['user_usr_id' => $user->usr_id, 'std_name' => $nome]);
    $registration = Registration::factory()->create([
        'student_std_id' => $student->std_id,
        'course_crs_id' => $course->crs_id,
        'curriculum_cur_id' => $curriculum->cur_id,
        'entry_term_trm_id' => $entryTerm->trm_id,
    ]);

    return ['user' => $user, 'registration' => $registration];
}

it('aluno recebe 403 em todos os endpoints do painel administrativo', function () {
    $this->actingAs($this->aluno)->getJson('/api/v1/staff/dashboard')->assertForbidden();
    $this->actingAs($this->aluno)->getJson('/api/v1/staff/students')->assertForbidden();
});

it('coordenador sem curso configurado recebe 422', function () {
    $semCurso = User::factory()->create(['usr_role' => UserRole::Coordinator, 'course_crs_id' => null]);

    $this->actingAs($semCurso)->getJson('/api/v1/staff/students')->assertStatus(422);
});

it('lista todos os alunos com o mesmo progresso de GET /me/progress', function () {
    $subject = Subject::factory()->create(['sbj_hours' => 60]);
    CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $subject->sbj_id, 'cbs_nature' => 'mandatory']);

    $felipe = alunoMatriculado($this->course, $this->curriculum, $this->termo, 'Felipe Kurt Pohling');
    SubjectEnrollment::factory()->approved(60)->create([
        'registration_reg_id' => $felipe['registration']->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->termo->trm_id,
    ]);

    $body = $this->actingAs($this->gestao)
        ->getJson('/api/v1/staff/students')
        ->assertOk()
        ->json('data');

    expect($body)->toHaveCount(1)
        ->and($body[0]['student_name'])->toBe('Felipe Kurt Pohling')
        ->and($body[0]['completed_hours'])->toBe(60);
});

it('coordenador só enxerga alunos do próprio curso na lista', function () {
    $outroCourse = Course::factory()->create(['campus_cps_id' => $this->campus->cps_id]);
    $outroCurriculum = Curriculum::factory()->create(['course_crs_id' => $outroCourse->crs_id]);

    alunoMatriculado($this->course, $this->curriculum, $this->termo, 'Do curso do coordenador');
    alunoMatriculado($outroCourse, $outroCurriculum, $this->termo, 'De outro curso');

    $body = $this->actingAs($this->coordenador)
        ->getJson('/api/v1/staff/students')
        ->assertOk()
        ->json('data');

    expect($body)->toHaveCount(1)
        ->and($body[0]['student_name'])->toBe('Do curso do coordenador');
});

it('o aluno 360 junta progresso, histórico, horas e o que libera — zero cálculo novo', function () {
    $subject = Subject::factory()->create(['sbj_code' => 'CALC1', 'sbj_hours' => 60]);
    CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $subject->sbj_id, 'cbs_nature' => 'mandatory']);

    $felipe = alunoMatriculado($this->course, $this->curriculum, $this->termo, 'Felipe Kurt Pohling');
    SubjectEnrollment::factory()->approved(60)->create([
        'registration_reg_id' => $felipe['registration']->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->termo->trm_id,
    ]);

    $this->actingAs($this->gestao)
        ->getJson("/api/v1/staff/students/{$felipe['registration']->reg_id}")
        ->assertOk()
        ->assertJsonPath('data.student.name', 'Felipe Kurt Pohling')
        ->assertJsonPath('data.progress.overall.completed_hours', 60)
        ->assertJsonPath('data.history.0.subject.code', 'CALC1')
        ->assertJsonCount(1, 'data.history')
        ->assertJsonPath('data.next_term.current_period', 1);
});

it('coordenador não acessa o aluno 360 de outro curso', function () {
    $outroCourse = Course::factory()->create(['campus_cps_id' => $this->campus->cps_id]);
    $outroCurriculum = Curriculum::factory()->create(['course_crs_id' => $outroCourse->crs_id]);
    $deOutroCurso = alunoMatriculado($outroCourse, $outroCurriculum, $this->termo, 'Não é do meu curso');

    $this->actingAs($this->coordenador)
        ->getJson("/api/v1/staff/students/{$deOutroCurso['registration']->reg_id}")
        ->assertForbidden();
});

it('o dashboard agrega estudantes por curso e a média de progresso', function () {
    $subject = Subject::factory()->create(['sbj_hours' => 100]);
    CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $subject->sbj_id, 'cbs_nature' => 'mandatory']);

    alunoMatriculado($this->course, $this->curriculum, $this->termo, 'Sem nenhuma disciplina');
    $comProgresso = alunoMatriculado($this->course, $this->curriculum, $this->termo, 'Com uma disciplina');
    SubjectEnrollment::factory()->approved(100)->create([
        'registration_reg_id' => $comProgresso['registration']->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->termo->trm_id,
    ]);

    $data = $this->actingAs($this->gestao)
        ->getJson('/api/v1/staff/dashboard')
        ->assertOk()
        ->json('data');

    expect($data['total_students'])->toBe(2)
        ->and($data['total_registrations'])->toBe(2)
        ->and($data['students_by_course'][0]['count'])->toBe(2)
        ->and($data['average_progress_percentage'])->toBeGreaterThan(0.0);
});
