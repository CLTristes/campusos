<?php

declare(strict_types=1);

namespace CampusOs\Journey\Support;

use CampusOs\Journey\Enums\EnrollmentStatus;

/**
 * Traduz o texto impresso no documento para o enum do sistema.
 *
 * Mora no `journey` e não no extrator de propósito: o extrator transcreve o que
 * está escrito, o domínio interpreta. Assim, trocar de provedor de IA não
 * arrasta o vocabulário acadêmico junto.
 *
 * A ordem das comparações importa — "Reprovado Por Nota/Frequência" contém
 * "Reprovado Por Nota", então o caso mais específico é testado primeiro.
 */
final class DocumentStatusTranslator
{
    public static function translate(string $printed): ?EnrollmentStatus
    {
        $n = self::normalize($printed);

        return match (true) {
            // Reprovações — do mais específico para o mais genérico.
            str_contains($n, 'reprovado por nota/frequencia'),
            str_contains($n, 'reprovado por nota e frequencia') => EnrollmentStatus::FailedBoth,
            str_contains($n, 'reprovado por frequencia') => EnrollmentStatus::FailedAbsence,
            str_contains($n, 'reprovado') => EnrollmentStatus::FailedGrade,

            // Aprovações.
            str_contains($n, 'exame de suficiencia') && str_contains($n, 'aprovado') => EnrollmentStatus::ApprovedExam,
            str_contains($n, 'aprovado') => EnrollmentStatus::Approved,

            str_contains($n, 'credito consignado') => EnrollmentStatus::Credited,
            str_contains($n, 'dispensa') || str_contains($n, 'dispensado') => EnrollmentStatus::Exempted,
            str_contains($n, 'cursando') || str_contains($n, 'matriculado') => EnrollmentStatus::Enrolled,
            str_contains($n, 'trancado') => EnrollmentStatus::Withdrawn,
            str_contains($n, 'cancelado') => EnrollmentStatus::Cancelled,

            // Desconhecido vira PENDÊNCIA de conferência, nunca um chute: o
            // aluno decide, em vez de o sistema inventar uma situação.
            default => null,
        };
    }

    /** minúsculas, sem acento e sem espaço duplicado — o documento varia. */
    private static function normalize(string $text): string
    {
        $t = mb_strtolower(trim($text));
        $t = strtr($t, [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'ê' => 'e', 'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ç' => 'c',
        ]);

        return preg_replace('/\s+/', ' ', $t) ?? $t;
    }
}
