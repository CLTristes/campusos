<?php

declare(strict_types=1);

use CampusOs\Catalog\Database\Seeders\MatrizUtfprSeeder;
use CampusOs\Catalog\Enums\PrerequisiteType;
use CampusOs\Catalog\Enums\SubjectNature;
use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use CampusOs\Catalog\Models\Prerequisite;
use CampusOs\Catalog\Models\Subject;
use CampusOs\Tenancy\Models\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * O GABARITO do catálogo.
 *
 * O documento "Consulta Curso e Matriz Curricular" imprime, no rodapé, os
 * totais de fechamento da matriz E a fórmula que os produz. Isso torna a
 * importação testável contra um valor que NÃO fomos nós que calculamos —
 * é o melhor teste de aceitação que existe, e veio de graça.
 *
 * Se estes números pararem de bater, ou a transcrição do CSV está errada, ou
 * alguém mexeu na regra de integralização sem querer.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
    Campus::factory()->create(['cps_code' => 'FB', 'cps_name' => 'Francisco Beltrão']);
    $this->seed(MatrizUtfprSeeder::class);
    $this->curriculum = Curriculum::query()->where('cur_code', '45')->firstOrFail();
});

it('reproduz o CHTOTALPPC de 3.000 h pela fórmula do documento', function () {
    // {CHTOTALPPC := SomaCHSemExt + (CHEXTENSAO - chext_discObrigatorias)}
    // {CHTOTALPPC := 2940 + (300 - 240)} => 3000
    expect($this->curriculum->totalRequiredHours())->toBe(3000);
});

it('as obrigatórias somam exatamente CHTOBRIGATORIASMATRIZ (2.730 h)', function () {
    $soma = CurriculumSubject::query()
        ->where('curriculum_cur_id', $this->curriculum->cur_id)
        ->where('cbs_nature', SubjectNature::Mandatory)
        ->join('subjects', 'subjects.sbj_id', '=', 'curriculum_subjects.subject_sbj_id')
        ->sum('subjects.sbj_hours');

    expect((int) $soma)
        ->toBe(2730)
        ->toBe($this->curriculum->cur_mandatory_hours);
});

it('a extensão embutida nas obrigatórias soma CHEXT_DISCOBRIGATORIAS (240 h)', function () {
    $soma = CurriculumSubject::query()
        ->where('curriculum_cur_id', $this->curriculum->cur_id)
        ->where('cbs_nature', SubjectNature::Mandatory)
        ->join('subjects', 'subjects.sbj_id', '=', 'curriculum_subjects.subject_sbj_id')
        ->sum('subjects.sbj_extension_hours');

    // E a diferença para as 300 h exigidas é exatamente a parcela autônoma —
    // a única que SOMA no total do curso.
    expect((int) $soma)->toBe(240)
        ->and($this->curriculum->cur_extension_hours - (int) $soma)
        ->toBe($this->curriculum->cur_standalone_extension_hours);
});

it('as cinco disciplinas que carregam extensão são as do documento', function () {
    $codes = Subject::query()
        ->where('sbj_extension_hours', '>', 0)
        ->whereIn('sbj_code', ['HSA003', 'HSA005', 'PEX304', 'PEX405', 'HCH023'])
        ->orderBy('sbj_code')
        ->pluck('sbj_extension_hours', 'sbj_code')
        ->all();

    expect($codes)->toBe([
        'HCH023' => 60, 'HSA003' => 45, 'HSA005' => 45, 'PEX304' => 30, 'PEX405' => 60,
    ]);
});

it('o estágio exige período mínimo, não disciplina', function () {
    $est501 = CurriculumSubject::query()
        ->whereRelation('subject', 'sbj_code', 'EST501')
        ->firstOrFail();

    $prq = Prerequisite::query()
        ->where('curriculum_subject_cbs_id', $est501->cbs_id)
        ->firstOrFail();

    expect($prq->prq_type)->toBe(PrerequisiteType::MinimumTerm)
        ->and($prq->prq_min_term)->toBe(5)
        ->and($prq->required_cbs_id)->toBeNull();
});

it('reprovar em LIP201 travaria sete disciplinas — a cadeia mais longa da matriz', function () {
    $lip201 = CurriculumSubject::query()
        ->whereRelation('subject', 'sbj_code', 'LIP201')
        ->firstOrFail();

    $travadas = $lip201->unlocks()
        ->with('curriculumSubject.subject')
        ->get()
        ->map(fn (Prerequisite $p): string => $p->curriculumSubject->subject->sbj_code)
        ->sort()
        ->values()
        ->all();

    expect($travadas)->toBe([
        'API003', 'EDD404', 'INC702', 'NEO001', 'NEO004', 'POO303', 'SDU601',
    ]);
});

it('o grafo de pré-requisitos não tem ciclo', function () {
    $edges = Prerequisite::query()
        ->where('prq_type', PrerequisiteType::Subject)
        ->get(['curriculum_subject_cbs_id', 'required_cbs_id'])
        ->groupBy('curriculum_subject_cbs_id')
        ->map(fn ($g) => $g->pluck('required_cbs_id')->all())
        ->all();

    $state = [];   // 0 = novo, 1 = na pilha, 2 = fechado
    $visit = function (string $node) use (&$visit, &$state, $edges): bool {
        if (($state[$node] ?? 0) === 1) {
            return false;                       // voltou para a própria pilha: ciclo
        }
        if (($state[$node] ?? 0) === 2) {
            return true;
        }
        $state[$node] = 1;
        foreach ($edges[$node] ?? [] as $next) {
            if (! $visit((string) $next)) {
                return false;
            }
        }
        $state[$node] = 2;

        return true;
    };

    foreach (array_keys($edges) as $node) {
        expect($visit((string) $node))->toBeTrue();
    }
});

it('o conjunto de optativas ocupa 3,5 CHS por período', function () {
    $group = $this->curriculum->electiveGroups()->where('elg_code', '941')->firstOrFail();

    // 14 CHS distribuídas nos períodos 5 a 8 — o documento avisa que pode dar
    // número não inteiro, e o histórico usa exatamente 3,50 no cálculo do período.
    expect($group->weeklyHoursPerTerm())->toBe(3.5)
        ->and($group->elg_required_hours)->toBe(210);
});
