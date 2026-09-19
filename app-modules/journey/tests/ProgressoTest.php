<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Enums\SubjectNature;
use CampusOs\Catalog\Models\Course;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\Student;
use CampusOs\Journey\Models\SubjectEnrollment;
use CampusOs\Journey\ReadModels\ProgressReadModel;
use CampusOs\Tenancy\Models\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
    Campus::factory()->create(['cps_code' => 'FB']);
    $this->seed(MatrizUtfprSeeder::class);

    $this->curriculum = Curriculum::query()->where('cur_code', '45')->firstOrFail();
    $this->course = Course::query()->where('crs_code', '25')->firstOrFail();
    $this->term = Term::query()->where('trm_year', 2023)->where('trm_period', 1)->firstOrFail();

    $this->registration = Registration::factory()->create([
        'student_std_id' => Student::factory()->create()->std_id,
        'course_crs_id' => $this->course->crs_id,
        'curriculum_cur_id' => $this->curriculum->cur_id,
        'entry_term_trm_id' => $this->term->trm_id,
        'reg_number' => '2567857',
    ]);

    $this->aprovar = function (string $code, ?Term $term = null): SubjectEnrollment {
        $subject = Subject::query()->where('sbj_code', $code)->firstOrFail();

        return SubjectEnrollment::factory()->create([
            'registration_reg_id' => $this->registration->reg_id,
            'subject_sbj_id' => $subject->sbj_id,
            'term_trm_id' => ($term ?? $this->term)->trm_id,
            'sen_status' => EnrollmentStatus::Approved,
            'sen_hours_earned' => $subject->sbj_hours,
            'sen_source' => EnrollmentSource::Transcript,
        ]);
    };
});

it('um vínculo sem histórico começa em zero, mas com o total certo', function () {
    $p = (new ProgressReadModel)->for($this->registration);

    expect($p['overall']['completed_hours'])->toBe(0)
        ->and($p['overall']['required_hours'])->toBe(3000)
        ->and($p['overall']['percentage'])->toBe(0.0)
        ->and($p['registration']['number'])->toBe('2567857')
        ->and($p['registration']['curriculum'])->toBe('45');
});

it('aprovação soma na faixa da NATUREZA que a matriz define', function () {
    ($this->aprovar)('ARC102');   // obrigatória, 60 h
    ($this->aprovar)('MAT015');   // optativa (conjunto 941), 60 h

    $p = (new ProgressReadModel)->for($this->registration);
    $tracks = collect($p['tracks'])->keyBy('kind');

    expect($tracks['mandatory']['completed'])->toBe(60)
        ->and($tracks['elective']['completed'])->toBe(60)
        ->and($p['overall']['completed_hours'])->toBe(120);
});

it('reprovação não soma nada', function () {
    $subject = Subject::query()->where('sbj_code', 'ARC102')->firstOrFail();
    SubjectEnrollment::factory()->failed()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->term->trm_id,
    ]);

    $p = (new ProgressReadModel)->for($this->registration);

    expect($p['overall']['completed_hours'])->toBe(0)
        ->and($p['forecast']['failures'])->toBe(1);
});

it('cursando conta como EM ANDAMENTO, nunca como cumprido', function () {
    $subject = Subject::query()->where('sbj_code', 'ARC102')->firstOrFail();
    SubjectEnrollment::factory()->enrolled()->create([
        'registration_reg_id' => $this->registration->reg_id,
        'subject_sbj_id' => $subject->sbj_id,
        'term_trm_id' => $this->term->trm_id,
    ]);

    $p = (new ProgressReadModel)->for($this->registration);
    $tracks = collect($p['tracks'])->keyBy('kind');

    // Tratar em-curso como cumprido é mentir para o aluno no pior momento.
    expect($p['overall']['completed_hours'])->toBe(0)
        ->and($tracks['mandatory']['in_progress'])->toBe(60);
});

it('crédito consignado e dispensa contam como cumprido', function () {
    foreach ([['FSI101', EnrollmentStatus::Credited], ['IES104', EnrollmentStatus::Exempted]] as [$code, $status]) {
        $subject = Subject::query()->where('sbj_code', $code)->firstOrFail();
        SubjectEnrollment::factory()->create([
            'registration_reg_id' => $this->registration->reg_id,
            'subject_sbj_id' => $subject->sbj_id,
            'term_trm_id' => $this->term->trm_id,
            'sen_status' => $status,
            'sen_hours_earned' => $subject->sbj_hours,
        ]);
    }

    // 60 + 60 — é assim que quem mudou de matriz não perde o que já cursou.
    expect((new ProgressReadModel)->for($this->registration)['overall']['completed_hours'])->toBe(120);
});

it('horas de uma natureza NÃO escorrem para outra', function () {
    // Cumpre optativa muito além do teto de 210 h...
    $optativas = CurriculumSubject::query()
        ->where('curriculum_cur_id', $this->curriculum->cur_id)
        ->where('cbs_nature', SubjectNature::Elective)
        ->with('subject')
        ->take(10)
        ->get();

    foreach ($optativas as $i => $cs) {
        SubjectEnrollment::factory()->create([
            'registration_reg_id' => $this->registration->reg_id,
            'subject_sbj_id' => $cs->subject_sbj_id,
            'term_trm_id' => $this->term->trm_id,
            'sen_status' => EnrollmentStatus::Approved,
            'sen_hours_earned' => $cs->subject->sbj_hours,
        ]);
    }

    $p = (new ProgressReadModel)->for($this->registration);
    $elective = collect($p['tracks'])->firstWhere('kind', 'elective');

    // ...e o excedente aparece como excedente, sem inflar o total.
    expect($elective['completed'])->toBeGreaterThan(210)
        ->and($elective['counted'])->toBe(210)
        ->and($elective['surplus'])->toBeGreaterThan(0)
        ->and($p['overall']['completed_hours'])->toBe(210);
});

it('a extensão é ortogonal — soma no indicador, nunca no total', function () {
    ($this->aprovar)('PEX304');   // 30 h, CHEXT 30
    ($this->aprovar)('HSA003');   // 45 h, CHEXT 45

    $p = (new ProgressReadModel)->for($this->registration);

    expect($p['extension']['completed'])->toBe(75)
        ->and($p['extension']['required'])->toBe(300)
        ->and($p['extension']['counts_in_total'])->toBeFalse()
        // 30 + 45 nas obrigatórias; a extensão NÃO é somada de novo.
        ->and($p['overall']['completed_hours'])->toBe(75);
});

it('lista o que falta, agrupado pelo período sugerido', function () {
    ($this->aprovar)('ARC102');

    $p = (new ProgressReadModel)->for($this->registration);
    $codes = collect($p['pending_subjects'])->pluck('code');

    expect($codes)->not->toContain('ARC102')
        ->and($codes)->toContain('FSI101')
        ->and(collect($p['pending_subjects'])->first()['term'])->toBe(1);
});

it('a previsão usa o ritmo REAL do aluno, não o ideal da matriz', function () {
    $t20231 = $this->term;
    $t20232 = Term::query()->where('trm_year', 2023)->where('trm_period', 2)->firstOrFail();

    // 300 h em 2023/1 e 300 h em 2023/2 → ritmo real de 300 h por semestre.
    foreach (['ARC102', 'FSI101', 'HCH021', 'IES104', 'MAT029'] as $code) {
        ($this->aprovar)($code, $t20231);
    }
    foreach (['FSO204', 'HCH022', 'LIP201', 'MAT034', 'RED202', 'REQ203'] as $code) {
        ($this->aprovar)($code, $t20232);
    }

    $p = (new ProgressReadModel)->for($this->registration);

    expect($p['forecast']['terms_attended'])->toBe(2)
        ->and($p['forecast']['avg_hours_per_term'])->toBe(300)
        ->and($p['overall']['completed_hours'])->toBe(600)
        // 2400 h restantes ÷ 300 por semestre = 8 semestres
        ->and($p['forecast']['remaining_terms'])->toBe(8);
});

it('o progresso de outra instituição nunca vaza', function () {
    ($this->aprovar)('ARC102');

    $outra = tenantContext();

    expect(Registration::query()->count())->toBe(0)
        ->and(SubjectEnrollment::query()->count())->toBe(0);
});
