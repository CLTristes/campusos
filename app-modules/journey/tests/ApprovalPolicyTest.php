<?php

declare(strict_types=1);

use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Support\ApprovalPolicy;

/**
 * A regra de aprovação, conferida contra os pares (média, frequência, situação)
 * legíveis no histórico escolar real — não contra exemplos inventados.
 */
it('abaixo de 50% de frequência, reprova independente da nota', function () {
    expect(ApprovalPolicy::decide(10.0, 49.9))->toBe(EnrollmentStatus::FailedAbsence)
        ->and(ApprovalPolicy::decide(9.5, 20.0))->toBe(EnrollmentStatus::FailedAbsence);
});

it('reprovado por nota E por falta é uma terceira situação, não duas', function () {
    // O documento distingue as três, e a coordenação trata cada uma de um jeito.
    expect(ApprovalPolicy::decide(3.0, 30.0))->toBe(EnrollmentStatus::FailedBoth);
});

it('entre 50% e 75%, a exigência de nota sobe para 8,0', function () {
    expect(ApprovalPolicy::requiredGrade(60.0))->toBe(8.0)
        ->and(ApprovalPolicy::decide(7.9, 60.0))->toBe(EnrollmentStatus::FailedGrade)
        ->and(ApprovalPolicy::decide(8.0, 60.0))->toBe(EnrollmentStatus::Approved);
});

it('de 75% para cima, 6,0 basta', function () {
    expect(ApprovalPolicy::requiredGrade(75.0))->toBe(6.0)
        ->and(ApprovalPolicy::decide(6.0, 75.0))->toBe(EnrollmentStatus::Approved)
        ->and(ApprovalPolicy::decide(5.9, 75.0))->toBe(EnrollmentStatus::FailedGrade);
});

it('o corte de 75% é fechado embaixo — 74,9 ainda exige 8,0', function () {
    expect(ApprovalPolicy::requiredGrade(74.9))->toBe(8.0)
        ->and(ApprovalPolicy::decide(7.0, 74.9))->toBe(EnrollmentStatus::FailedGrade)
        ->and(ApprovalPolicy::decide(7.0, 75.0))->toBe(EnrollmentStatus::Approved);
});

it('disciplina sem aula não tem frequência — e zero não é null', function () {
    // EST501 (Estágio) no histórico real: frequência 0,0 e APROVADO. Tratar
    // esse zero como falta reprovaria todo mundo que fez estágio.
    expect(ApprovalPolicy::decide(10.0, null))->toBe(EnrollmentStatus::Approved)
        ->and(ApprovalPolicy::decide(10.0, 0.0))->toBe(EnrollmentStatus::FailedAbsence);
});

it('reproduz as situações do histórico real', function (float $grade, float $attendance, EnrollmentStatus $esperado) {
    expect(ApprovalPolicy::decide($grade, $attendance))->toBe($esperado);
})->with([
    'HLA001 · 10,0 com 67,9%' => [10.0, 67.9, EnrollmentStatus::Approved],
    'GEP701 · 8,2 com 64,7%' => [8.2, 64.7, EnrollmentStatus::Approved],
    'SIN703 · 8,5 com 70,6%' => [8.5, 70.6, EnrollmentStatus::Approved],
    'HSA001 · 9,5 com 70,6%' => [9.5, 70.6, EnrollmentStatus::Approved],
    'SDU601 · 6,0 com 77,6%' => [6.0, 77.6, EnrollmentStatus::Approved],
    'PIN603 · 7,3 com 94,4%' => [7.3, 94.4, EnrollmentStatus::Approved],
    'MAT032 · 0,0 com 47,1%' => [0.0, 47.1, EnrollmentStatus::FailedBoth],
]);

it('o rótulo do documento revela a faixa de frequência', function () {
    // Sem exceção nos 14 aprovados do histórico real.
    expect(ApprovalPolicy::attendanceRangeFromLabel('Aprovado Por Nota/Frequência'))->toBe([75.0, null])
        ->and(ApprovalPolicy::attendanceRangeFromLabel('Aprovado Por Nota'))->toBe([50.0, 75.0])
        ->and(ApprovalPolicy::attendanceRangeFromLabel('Reprovado Por Nota'))->toBeNull();
});

it('avisa enquanto ainda dá tempo', function () {
    expect(ApprovalPolicy::isLostByAttendance(49.0))->toBeTrue()
        ->and(ApprovalPolicy::isLostByAttendance(50.0))->toBeFalse();
});
