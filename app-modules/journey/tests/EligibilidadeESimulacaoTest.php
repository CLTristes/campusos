<?php

declare(strict_types=1);

use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Prerequisite;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();

    $this->course = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $this->curriculum = Curriculum::factory()->create(['course_crs_id' => $this->course->crs_id]);

    // A cadeia mais longa da matriz, em miniatura: CALC1 -> CALC2 -> CALC3 -> FENOM
    // (mesmo espírito de LIP201 -> POO303 -> WBE501 na matriz 45 real).
    $this->calc1 = Subject::factory()->create(['sbj_code' => 'CALC1']);
    $this->calc2 = Subject::factory()->create(['sbj_code' => 'CALC2']);
    $this->calc3 = Subject::factory()->create(['sbj_code' => 'CALC3']);
    $this->fenom = Subject::factory()->create(['sbj_code' => 'FENOM']);
    $this->estagio = Subject::factory()->create(['sbj_code' => 'EST501']);

    $this->csCalc1 = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $this->calc1->sbj_id, 'cbs_term' => 1]);
    $this->csCalc2 = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $this->calc2->sbj_id, 'cbs_term' => 2]);
    $this->csCalc3 = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $this->calc3->sbj_id, 'cbs_term' => 3]);
    $this->csFenom = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $this->fenom->sbj_id, 'cbs_term' => 4]);
    $this->csEstagio = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $this->estagio->sbj_id, 'cbs_term' => 5]);

    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $this->csCalc2->cbs_id, 'required_cbs_id' => $this->csCalc1->cbs_id]);
    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $this->csCalc3->cbs_id, 'required_cbs_id' => $this->csCalc2->cbs_id]);
    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $this->csFenom->cbs_id, 'required_cbs_id' => $this->csCalc3->cbs_id]);
    Prerequisite::factory()->minimumTerm(5)->create(['curriculum_subject_cbs_id' => $this->csEstagio->cbs_id]);

    $this->termo = Term::factory()->create(['trm_year' => 2026, 'trm_period' => 1]);
    $this->user = User::factory()->create();
    $this->registration = Registration::factory()->create([
        'student_std_id' => Student::factory()->create(['user_usr_id' => $this->user->usr_id])->std_id,
        'course_crs_id' => $this->course->crs_id,
        'curriculum_cur_id' => $this->curriculum->cur_id,
        'entry_term_trm_id' => $this->termo->trm_id,
    ]);
});

function aprovarNaCadeia(Registration $registration, Subject $subject, Term $term): SubjectEnrollment
{
    return SubjectEnrollment::factory()->approved()->create([
        'registration_reg_id' => $registration->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $term->trm_id,
    ]);
}

it('libera a próxima disciplina só depois de aprovar a que ela exige', function () {
    aprovarNaCadeia($this->registration, $this->calc1, $this->termo);

    $body = $this->actingAs($this->user)
        ->getJson('/api/v1/me/next-term')
        ->assertOk()
        ->json('data');

    expect(collect($body['eligible'])->pluck('code'))->toContain('CALC2')
        ->and(collect($body['blocked'])->pluck('code'))->toContain('CALC3');

    $travada = collect($body['blocked'])->firstWhere('code', 'CALC3');
    expect($travada['blocked_by'][0]['type'])->toBe('subject')
        ->and($travada['blocked_by'][0]['subject']['code'])->toBe('CALC2');
});

it('minimum_term trava até o aluno atingir o período exigido — o caso EST501', function () {
    aprovarNaCadeia($this->registration, $this->calc1, $this->termo);

    $body = $this->actingAs($this->user)
        ->getJson('/api/v1/me/next-term')
        ->assertOk()
        ->json('data');

    expect($body['current_period'])->toBe(1);

    $travada = collect($body['blocked'])->firstWhere('code', 'EST501');
    expect($travada['blocked_by'][0]['type'])->toBe('minimum_term')
        ->and($travada['blocked_by'][0]['reason'])->toContain('5º período');
});

it('disciplina já aprovada ou cursando não aparece nem em liberadas nem em travadas', function () {
    aprovarNaCadeia($this->registration, $this->calc1, $this->termo);
    SubjectEnrollment::factory()->enrolled()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'subject_sbj_id' => $this->calc2->sbj_id,
        'term_trm_id' => $this->termo->trm_id,
    ]);

    $body = $this->actingAs($this->user)
        ->getJson('/api/v1/me/next-term')
        ->assertOk()
        ->json('data');

    $codigos = collect($body['eligible'])->pluck('code')->concat(collect($body['blocked'])->pluck('code'));
    expect($codigos)->not->toContain('CALC1')->not->toContain('CALC2');
});

it('simular reprovação empurra em cascata toda a cadeia que depende da disciplina', function () {
    aprovarNaCadeia($this->registration, $this->calc1, $this->termo);

    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/me/simulate', ['fail' => ['CALC1']])
        ->assertOk()
        ->json('data');

    $codigosAfetados = collect($body['affected_subjects'])->pluck('subject.code');

    expect($codigosAfetados)->toContain('CALC2', 'CALC3', 'FENOM')
        ->and($body['estimated_graduation_delay_terms'])->toBe(1);

    $calc2 = collect($body['affected_subjects'])->firstWhere('subject.code', 'CALC2');
    expect($calc2['delay_terms'])->toBe(1)
        ->and($calc2['simulated_earliest_period'])->toBe($calc2['real_earliest_period'] + 1);
});

it('simular reprovação em disciplina fora de qualquer cadeia não afeta nada', function () {
    aprovarNaCadeia($this->registration, $this->calc1, $this->termo);
    aprovarNaCadeia($this->registration, $this->calc2, $this->termo);
    aprovarNaCadeia($this->registration, $this->calc3, $this->termo);

    $isolada = Subject::factory()->create(['sbj_code' => 'ISOLADA']);
    CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $isolada->sbj_id, 'cbs_term' => 1]);
    aprovarNaCadeia($this->registration, $isolada, $this->termo);

    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/me/simulate', ['fail' => ['ISOLADA']])
        ->assertOk()
        ->json('data');

    expect($body['affected_subjects'])->toBeEmpty()
        ->and($body['estimated_graduation_delay_terms'])->toBe(0);
});

it('ciclo em pré-requisito não trava o sistema — protegido pelo conjunto de visitados', function () {
    // Erro de cadastro no colegiado: A exige B, B exige A.
    $a = Subject::factory()->create(['sbj_code' => 'CICLOA']);
    $b = Subject::factory()->create(['sbj_code' => 'CICLOB']);
    $csA = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $a->sbj_id, 'cbs_term' => 3]);
    $csB = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $b->sbj_id, 'cbs_term' => 3]);
    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $csA->cbs_id, 'required_cbs_id' => $csB->cbs_id]);
    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $csB->cbs_id, 'required_cbs_id' => $csA->cbs_id]);

    $this->actingAs($this->user)
        ->getJson('/api/v1/me/next-term')
        ->assertOk();

    $this->actingAs($this->user)
        ->postJson('/api/v1/me/simulate', ['fail' => ['CALC1']])
        ->assertOk();
});

it('simular reprovação em disciplina que não existe é recusado', function () {
    $this->actingAs($this->user)
        ->postJson('/api/v1/me/simulate', ['fail' => ['NAOEXISTE']])
        ->assertStatus(422)
        ->assertJsonValidationErrors('fail.0');
});

it('sem vínculo acadêmico, next-term devolve 404', function () {
    $semVinculo = User::factory()->create();

    $this->actingAs($semVinculo)
        ->getJson('/api/v1/me/next-term')
        ->assertNotFound();
});
