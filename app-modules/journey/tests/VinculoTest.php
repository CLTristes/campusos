<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Actions\CreateRegistrationAction;
use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->entity = tenantContext();
    Campus::factory()->create(['cps_code' => 'FB']);
    $this->seed(MatrizUtfprSeeder::class);

    $this->course = Course::query()->where('crs_code', '25')->firstOrFail();
    $this->term = Term::query()->where('trm_year', 2023)->where('trm_period', 1)->firstOrFail();
    $this->student = Student::factory()->create();
});

it('a matriz é resolvida no servidor e congelada no vínculo', function () {
    $reg = app(CreateRegistrationAction::class)->execute([
        'student_id' => $this->student->std_id,
        'course_id' => $this->course->crs_id,
        'entry_term_id' => $this->term->trm_id,
        'number' => '2567857',
    ]);

    $vigente = Curriculum::query()->where('cur_code', '45')->firstOrFail();

    expect($reg->curriculum_cur_id)->toBe($vigente->cur_id)
        ->and($reg->reg_number)->toBe('2567857')
        ->and($reg->entity_ent_id)->toBe($this->entity->ent_id);
});

it('a matriz NÃO vem do cliente', function () {
    // Mesmo mandando uma matriz na entrada, ela é ignorada: quem escolhe a
    // matriz escolhe o próprio critério de formatura.
    $reg = app(CreateRegistrationAction::class)->execute([
        'student_id' => $this->student->std_id,
        'course_id' => $this->course->crs_id,
        'entry_term_id' => $this->term->trm_id,
        'number' => '2567858',
        'curriculum_id' => '00000000-0000-0000-0000-000000000000',
    ]);

    expect($reg->curriculum_cur_id)->not->toBe('00000000-0000-0000-0000-000000000000');
});

it('uma reforma curricular NÃO reescreve o vínculo de quem já ingressou', function () {
    $reg = app(CreateRegistrationAction::class)->execute([
        'student_id' => $this->student->std_id,
        'course_id' => $this->course->crs_id,
        'entry_term_id' => $this->term->trm_id,
        'number' => '2567857',
    ]);
    $matrizDoIngresso = $reg->curriculum_cur_id;

    // O colegiado publica matriz nova em 2026.
    Curriculum::factory()->create([
        'course_crs_id' => $this->course->crs_id,
        'cur_code' => '46',
        'cur_effective_from' => '2026-01-01',
    ]);

    // A barra de progresso deste aluno não pode mudar de valor sozinha.
    expect($reg->fresh()->curriculum_cur_id)->toBe($matrizDoIngresso);

    // Mas quem ingressar AGORA pega a nova.
    $t2026 = Term::query()->where('trm_year', 2026)->where('trm_period', 1)->firstOrFail();
    $novo = app(CreateRegistrationAction::class)->execute([
        'student_id' => Student::factory()->create()->std_id,
        'course_id' => $this->course->crs_id,
        'entry_term_id' => $t2026->trm_id,
        'number' => '2600001',
    ]);

    expect($novo->curriculum_cur_id)->not->toBe($matrizDoIngresso);
});

it('o mesmo aluno pode ter dois vínculos, cada um com sua matriz', function () {
    foreach (['2567857', '2600002'] as $numero) {
        app(CreateRegistrationAction::class)->execute([
            'student_id' => $this->student->std_id,
            'course_id' => $this->course->crs_id,
            'entry_term_id' => $this->term->trm_id,
            'number' => $numero,
        ]);
    }

    expect($this->student->registrations()->count())->toBe(2);
});

it('cursar de novo em OUTRO semestre gera linha nova — é assim que reprovação aparece', function () {
    $reg = Registration::factory()->create([
        'student_std_id' => $this->student->std_id,
        'course_crs_id' => $this->course->crs_id,
        'curriculum_cur_id' => Curriculum::query()->where('cur_code', '45')->value('cur_id'),
        'entry_term_trm_id' => $this->term->trm_id,
    ]);
    $subject = Subject::query()->where('sbj_code', 'LIP201')->firstOrFail();
    $t20232 = Term::query()->where('trm_year', 2023)->where('trm_period', 2)->firstOrFail();

    SubjectEnrollment::factory()->failed()->create([
        'registration_reg_id' => $reg->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->term->trm_id,
    ]);
    SubjectEnrollment::factory()->approved(90)->create([
        'registration_reg_id' => $reg->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $t20232->trm_id,
    ]);

    $linhas = SubjectEnrollment::query()->where('subject_sbj_id', $subject->sbj_id)->get();

    expect($linhas)->toHaveCount(2)
        ->and($linhas->pluck('sen_status')->all())
        ->toBe([EnrollmentStatus::FailedGrade, EnrollmentStatus::Approved]);
});

it('mas a MESMA disciplina no MESMO semestre é barrada pelo banco', function () {
    $reg = Registration::factory()->create([
        'student_std_id' => $this->student->std_id,
        'course_crs_id' => $this->course->crs_id,
        'curriculum_cur_id' => Curriculum::query()->where('cur_code', '45')->value('cur_id'),
        'entry_term_trm_id' => $this->term->trm_id,
    ]);
    $subject = Subject::query()->where('sbj_code', 'ARC102')->firstOrFail();

    $payload = [
        'registration_reg_id' => $reg->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->term->trm_id,
    ];
    SubjectEnrollment::factory()->create($payload);

    expect(fn () => SubjectEnrollment::factory()->create($payload))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('GET /me/progress devolve a progressão do aluno autenticado', function () {
    $user = User::factory()->create();
    $this->student->update(['user_usr_id' => $user->usr_id]);

    app(CreateRegistrationAction::class)->execute([
        'student_id' => $this->student->std_id,
        'course_id' => $this->course->crs_id,
        'entry_term_id' => $this->term->trm_id,
        'number' => '2567857',
    ]);

    $this->actingAs($user)->getJson('/api/v1/me/progress')
        ->assertOk()
        ->assertJsonPath('data.registration.number', '2567857')
        ->assertJsonPath('data.overall.required_hours', 3000)
        ->assertJsonPath('data.extension.counts_in_total', false)
        ->assertJsonCount(3, 'data.tracks');
});

it('aluno sem vínculo recebe 404 com instrução do que fazer', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/me/progress')
        ->assertNotFound()
        ->assertJsonPath('message', 'Você ainda não tem vínculo acadêmico. Envie seu histórico escolar.');
});

it('sem token, a progressão é recusada', function () {
    $this->getJson('/api/v1/me/progress')->assertStatus(401);
});
