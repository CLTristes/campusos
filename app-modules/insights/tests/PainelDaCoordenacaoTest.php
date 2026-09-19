<?php

declare(strict_types=1);

use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Prerequisite;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Enums\RegistrationStatus;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();

    $this->course = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $this->curriculum = Curriculum::factory()->create([
        'course_crs_id' => $this->course->crs_id,
        'cur_expected_terms' => 4,
        'cur_max_terms' => 8,
    ]);

    $this->coordenador = User::factory()->create(['usr_role' => UserRole::Coordinator, 'course_crs_id' => $this->course->crs_id]);
    $this->gestao = User::factory()->create(['usr_role' => UserRole::InstitutionAdmin]);
    $this->aluno = User::factory()->create(['usr_role' => UserRole::Student]);
});

/** @return array{user: User, registration: Registration} */
function vinculoAtivo(Curriculum $curriculum, Term $entryTerm, RegistrationStatus $status = RegistrationStatus::Active): array
{
    $user = User::factory()->create(['usr_role' => UserRole::Student]);
    $student = Student::factory()->create(['user_usr_id' => $user->usr_id]);
    $registration = Registration::factory()->create([
        'student_std_id' => $student->std_id,
        'course_crs_id' => $curriculum->course_crs_id,
        'curriculum_cur_id' => $curriculum->cur_id,
        'entry_term_trm_id' => $entryTerm->trm_id,
        'reg_status' => $status,
    ]);

    return ['user' => $user, 'registration' => $registration];
}

function matricularEm(Registration $registration, Subject $subject, Term $term, string $status = 'approved'): SubjectEnrollment
{
    return SubjectEnrollment::factory()->create([
        'registration_reg_id' => $registration->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $term->trm_id,
        'sen_status' => $status,
        'sen_hours_earned' => $status === 'approved' ? 60 : 0,
    ]);
}

it('aluno recebe 403 em todos os endpoints do painel', function () {
    $this->actingAs($this->aluno)->getJson('/api/v1/staff/insights/bottlenecks')->assertForbidden();
    $this->actingAs($this->aluno)->getJson('/api/v1/staff/insights/cohorts')->assertForbidden();
    $this->actingAs($this->aluno)->getJson('/api/v1/staff/insights/at-risk')->assertForbidden();
    $this->actingAs($this->aluno)->getJson('/api/v1/staff/insights/demand')->assertForbidden();
});

it('coordenador sem curso configurado recebe 422', function () {
    $semCurso = User::factory()->create(['usr_role' => UserRole::Coordinator, 'course_crs_id' => null]);

    $this->actingAs($semCurso)
        ->getJson('/api/v1/staff/insights/bottlenecks')
        ->assertStatus(422);
});

it('taxa de reprovação por disciplina — piso de anonimato e nota/falta separadas', function () {
    $termoRecente = Term::factory()->create(['trm_year' => 2026, 'trm_period' => 1]);
    $termoAntigo = Term::factory()->create(['trm_year' => 2020, 'trm_period' => 1]);

    $gargalo = Subject::factory()->create(['sbj_code' => 'MAT029', 'sbj_name' => 'Cálculo 2']);
    $poucaAmostra = Subject::factory()->create(['sbj_code' => 'ISOLADA']);

    // 5 tentativas resolvidas no termo recente: 3 aprovadas, 2 reprovadas por nota.
    foreach ([true, true, true, false, false] as $aprovado) {
        $v = vinculoAtivo($this->curriculum, $termoRecente);
        matricularEm($v['registration'], $gargalo, $termoRecente, $aprovado ? 'approved' : 'failed_grade');
    }

    // Termo ANTIGO — fora da janela de last_terms=1, não deve contar.
    $v = vinculoAtivo($this->curriculum, $termoAntigo);
    matricularEm($v['registration'], $gargalo, $termoAntigo, 'failed_grade');

    // Amostra pequena — abaixo do piso de anonimato (5).
    foreach (range(1, 3) as $i) {
        $v = vinculoAtivo($this->curriculum, $termoRecente);
        matricularEm($v['registration'], $poucaAmostra, $termoRecente, 'failed_grade');
    }

    $body = $this->actingAs($this->coordenador)
        ->getJson('/api/v1/staff/insights/bottlenecks?last_terms=1')
        ->assertOk()
        ->json('data');

    expect($body)->toHaveCount(1)
        ->and($body[0]['subject_code'])->toBe('MAT029')
        ->and($body[0]['total_attempts'])->toBe(5)
        ->and($body[0]['failed_grade'])->toBe(2)
        ->and($body[0]['failure_rate'])->toBe(0.4);
});

it('atraso médio por coorte — só coortes com pelo menos 5 vínculos ativos aparecem', function () {
    $entrada2024 = Term::factory()->create(['trm_year' => 2024, 'trm_period' => 1]);
    $entrada2025 = Term::factory()->create(['trm_year' => 2025, 'trm_period' => 1]);
    // Termos cursados — anos bem à frente das entradas, pra nunca colidir.
    $termos = collect(range(1, 6))->map(fn ($i) => Term::factory()->create(['trm_year' => 2029 + intdiv($i - 1, 2), 'trm_period' => $i % 2 === 0 ? 2 : 1]));
    $subject = Subject::factory()->create();

    // Coorte 2024/1: 5 vínculos ativos, cada um com 6 termos distintos
    // cursados (expected=4 => atraso de 2 termos cada).
    foreach (range(1, 5) as $i) {
        $v = vinculoAtivo($this->curriculum, $entrada2024);
        foreach ($termos->take(6) as $termo) {
            matricularEm($v['registration'], $subject, $termo);
        }
    }

    // Coorte 2025/1: só 3 vínculos — abaixo do piso, não deve aparecer.
    foreach (range(1, 3) as $i) {
        $v = vinculoAtivo($this->curriculum, $entrada2025);
        matricularEm($v['registration'], $subject, $termos->first());
    }

    $body = $this->actingAs($this->coordenador)
        ->getJson('/api/v1/staff/insights/cohorts')
        ->assertOk()
        ->json('data');

    expect($body)->toHaveCount(1)
        ->and($body[0]['entry_term'])->toBe('2024/1')
        ->and($body[0]['active_registrations'])->toBe(5)
        ->and((float) $body[0]['avg_delay_terms'])->toBe(2.0);
});

it('quem está perto do limite — conta só quem cabe na janela informada', function () {
    $entrada = Term::factory()->create(['trm_year' => 2020, 'trm_period' => 1]);
    $termos = collect(range(1, 7))->map(fn ($i) => Term::factory()->create(['trm_year' => 2020 + $i, 'trm_period' => 1]));
    $subject = Subject::factory()->create();

    // 7 termos cursados, max_terms=8 => terms_until_deadline=1 (perto do limite).
    $perto = vinculoAtivo($this->curriculum, $entrada);
    foreach ($termos as $termo) {
        matricularEm($perto['registration'], $subject, $termo);
    }

    // 2 termos cursados => terms_until_deadline=6 (longe).
    $longe = vinculoAtivo($this->curriculum, $entrada);
    foreach ($termos->take(2) as $termo) {
        matricularEm($longe['registration'], $subject, $termo);
    }

    $body = $this->actingAs($this->coordenador)
        ->getJson('/api/v1/staff/insights/at-risk?within=2')
        ->assertOk()
        ->json('data');

    expect($body['count'])->toBe(1)
        ->and($body['within_terms'])->toBe(2);
});

it('demanda pro próximo período — só disciplinas com pelo menos 5 elegíveis aparecem', function () {
    $termo = Term::factory()->create(['trm_year' => 2026, 'trm_period' => 1]);
    $x = Subject::factory()->create(['sbj_code' => 'X001']);
    $y = Subject::factory()->create(['sbj_code' => 'Y002']);
    $csX = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $x->sbj_id, 'cbs_term' => 1]);
    $csY = CurriculumSubject::factory()->create(['curriculum_cur_id' => $this->curriculum->cur_id, 'subject_sbj_id' => $y->sbj_id, 'cbs_term' => 2]);
    Prerequisite::factory()->create(['curriculum_subject_cbs_id' => $csY->cbs_id, 'required_cbs_id' => $csX->cbs_id]);

    // 5 vínculos já aprovaram X -> elegíveis para Y.
    foreach (range(1, 5) as $i) {
        $v = vinculoAtivo($this->curriculum, $termo);
        matricularEm($v['registration'], $x, $termo, 'approved');
    }

    // 2 vínculos aprovaram Y direto (não entra mais em "eligible" nem em "pendente") —
    // e X não aparece pendente pra ninguém, então X nunca é "eligible" aqui.
    foreach (range(1, 2) as $i) {
        $v = vinculoAtivo($this->curriculum, $termo);
        matricularEm($v['registration'], $x, $termo, 'approved');
        matricularEm($v['registration'], $y, $termo, 'approved');
    }

    $body = $this->actingAs($this->coordenador)
        ->getJson('/api/v1/staff/insights/demand')
        ->assertOk()
        ->json('data');

    expect(collect($body)->pluck('subject_code'))->toContain('Y002')
        ->and(collect($body)->firstWhere('subject_code', 'Y002')['demand'])->toBe(5);
});

it('coordenador de um curso nunca vê o gargalo de outro curso', function () {
    $termo = Term::factory()->create(['trm_year' => 2026, 'trm_period' => 1]);
    $outroCurso = Course::factory()->create(['campus_cps_id' => Campus::factory()->create()->cps_id]);
    $outraCurricula = Curriculum::factory()->create(['course_crs_id' => $outroCurso->crs_id]);
    $gargaloAlheio = Subject::factory()->create(['sbj_code' => 'ALHEIA']);

    foreach (range(1, 5) as $i) {
        $v = vinculoAtivo($outraCurricula, $termo);
        matricularEm($v['registration'], $gargaloAlheio, $termo, 'failed_grade');
    }

    $body = $this->actingAs($this->coordenador)
        ->getJson('/api/v1/staff/insights/bottlenecks')
        ->assertOk()
        ->json('data');

    expect(collect($body)->pluck('subject_code'))->not->toContain('ALHEIA');
});

it('institution_admin nunca enxerga o gargalo nem a coorte de OUTRA instituição', function () {
    // DB::table() não aplica EntityScope — a query precisa filtrar
    // entity_ent_id na mão, senão courseId=null (o escopo do institution_admin)
    // vaza a tabela inteira, de todas as instituições.
    $termo = Term::factory()->create(['trm_year' => 2026, 'trm_period' => 1]);
    $entradaDaqui = Term::factory()->create(['trm_year' => 2024, 'trm_period' => 1]);
    $gargaloDaqui = Subject::factory()->create(['sbj_code' => 'DAQUI']);
    $gargaloAlheio = Subject::factory()->create(['sbj_code' => 'ALHEIA']);

    // Dado real NESTA instituição — pra provar que a query não fica vazia à
    // toa, e que o que aparece é só o que é desta instituição.
    foreach (range(1, 5) as $i) {
        $v = vinculoAtivo($this->curriculum, $entradaDaqui);
        matricularEm($v['registration'], $gargaloDaqui, $termo, 'failed_grade');
    }

    $outraEntidade = Entity::factory()->create();
    CampusOs\Core\Tenancy\TenantContext::runAs($outraEntidade->ent_id, function () use ($gargaloAlheio): void {
        $outroCampus = Campus::factory()->create();
        $outroCurso = Course::factory()->create(['campus_cps_id' => $outroCampus->cps_id]);
        $outraCurricula = Curriculum::factory()->create(['course_crs_id' => $outroCurso->crs_id]);
        $outroTermo = Term::factory()->create(['trm_year' => 2026, 'trm_period' => 1]);

        foreach (range(1, 5) as $i) {
            $v = vinculoAtivo($outraCurricula, $outroTermo);
            matricularEm($v['registration'], $gargaloAlheio, $outroTermo, 'failed_grade');
        }
    });

    $bottlenecks = $this->actingAs($this->gestao)
        ->getJson('/api/v1/staff/insights/bottlenecks')
        ->assertOk()
        ->json('data');
    $cohorts = $this->actingAs($this->gestao)
        ->getJson('/api/v1/staff/insights/cohorts')
        ->assertOk()
        ->json('data');

    expect(collect($bottlenecks)->pluck('subject_code'))->toContain('DAQUI')->not->toContain('ALHEIA')
        ->and(collect($cohorts)->pluck('entry_term'))->toContain('2024/1');
});

it('institution_admin enxerga a instituição inteira, sem curso configurado', function () {
    $this->actingAs($this->gestao)
        ->getJson('/api/v1/staff/insights/bottlenecks')
        ->assertOk()
        ->assertJsonPath('data', []);
});
