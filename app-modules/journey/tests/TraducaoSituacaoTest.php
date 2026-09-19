<?php

declare(strict_types=1);

use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Support\DocumentStatusTranslator;

/**
 * Tradução do texto impresso para o enum. Todos os casos vêm dos documentos
 * reais da UTFPR (docs/dominio/DOCUMENTOS_ACADEMICOS.md §1.3).
 */
it('traduz as situações que o histórico imprime', function (string $texto, EnrollmentStatus $esperado) {
    expect(DocumentStatusTranslator::translate($texto))->toBe($esperado);
})->with([
    'Aprovado Por Nota/Frequência' => ['Aprovado Por Nota/Frequência', EnrollmentStatus::Approved],
    'Aprovado Por Nota' => ['Aprovado Por Nota', EnrollmentStatus::Approved],
    'Aprovado Em Exame De Suficiência' => ['Aprovado Em Exame De Suficiência', EnrollmentStatus::ApprovedExam],
    'Crédito Consignado' => ['Crédito Consignado', EnrollmentStatus::Credited],
    'Reprovado Por Nota' => ['Reprovado Por Nota', EnrollmentStatus::FailedGrade],
    'Reprovado Por Nota/Frequência' => ['Reprovado Por Nota/Frequência', EnrollmentStatus::FailedBoth],
    'Reprovado Por Frequência' => ['Reprovado Por Frequência', EnrollmentStatus::FailedAbsence],
    'Cancelado' => ['Cancelado', EnrollmentStatus::Cancelled],
    'Cursando' => ['Cursando', EnrollmentStatus::Enrolled],
    'ENADE dispensado' => ['Enade - Estudante Dispensado De Realização Do Enade', EnrollmentStatus::Exempted],
]);

it('não confunde reprovação dupla com reprovação por nota', function () {
    // "Reprovado Por Nota/Frequência" CONTÉM "Reprovado Por Nota" — se a ordem
    // das comparações estiver errada, toda reprovação dupla vira só por nota.
    expect(DocumentStatusTranslator::translate('Reprovado Por Nota/Frequência'))
        ->toBe(EnrollmentStatus::FailedBoth)
        ->and(DocumentStatusTranslator::translate('Reprovado Por Nota'))
        ->toBe(EnrollmentStatus::FailedGrade);
});

it('tolera acento, caixa e espaço a mais — o documento varia', function () {
    foreach (['APROVADO POR NOTA', 'aprovado  por   nota', 'Aprovado Por Nota '] as $variacao) {
        expect(DocumentStatusTranslator::translate($variacao))->toBe(EnrollmentStatus::Approved);
    }
});

it('situação desconhecida vira null, nunca um chute', function () {
    // Vira pendência de conferência: o aluno decide, o sistema não inventa.
    expect(DocumentStatusTranslator::translate('Situação Esquisita'))->toBeNull()
        ->and(DocumentStatusTranslator::translate(''))->toBeNull();
});
