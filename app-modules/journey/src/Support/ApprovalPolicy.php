<?php

declare(strict_types=1);

namespace CampusOs\Journey\Support;

use CampusOs\Journey\Enums\EnrollmentStatus;

/**
 * A regra de aprovação da UTFPR — frequência primeiro, nota depois.
 * (docs/dominio/PROGRESSAO.md regras 5 e 6)
 *
 *   frequência  < 50 %        reprovado por frequência, independente da nota
 *   50 % ≤ f < 75 %           aprovado só com média ≥ 8,0
 *   frequência ≥ 75 %         aprovado com média ≥ 6,0
 *
 * ATENÇÃO — esta classe NÃO decide a situação de uma matrícula importada. O
 * sistema grava o que o documento oficial imprime, sempre. Isto aqui serve para
 * três outras coisas:
 *
 *   1. o alerta preventivo ("você está com 62 % — daqui em diante precisa de
 *      8,0, não 6,0"), que é informação que ninguém dá ao aluno a tempo;
 *   2. a simulação de reprovação;
 *   3. conferir a importação — divergir do impresso é sinal de leitura errada
 *      do documento, não de regra errada.
 *
 * Ver a pendência 1 de PROGRESSAO.md: uma disciplina do histórico real
 * (ISI604, média 7,4 com 73 %) contradiz a faixa do meio e está em aberto.
 */
final class ApprovalPolicy
{
    public const MIN_ATTENDANCE = 50.0;

    public const FULL_ATTENDANCE = 75.0;

    public const PASSING_GRADE = 6.0;

    /** Na faixa 50–75 % a exigência de nota sobe. */
    public const PASSING_GRADE_LOW_ATTENDANCE = 8.0;

    /**
     * A situação que a regra prevê.
     *
     * @param  ?float  $attendance  null = não se aplica (disciplina sem aula:
     *                              estágio, TCC, atividades complementares,
     *                              ENADE). Zero NÃO é o mesmo que null — no
     *                              histórico real o estágio vem com 0,0 e é
     *                              aprovado; tratar como falta reprovaria todo
     *                              mundo que fez estágio.
     */
    public static function decide(?float $grade, ?float $attendance): EnrollmentStatus
    {
        $grade ??= 0.0;

        if ($attendance === null) {
            return $grade >= self::PASSING_GRADE
                ? EnrollmentStatus::Approved
                : EnrollmentStatus::FailedGrade;
        }

        if ($attendance < self::MIN_ATTENDANCE) {
            // Abaixo de 50 % a nota é irrelevante. Se a nota TAMBÉM não passa,
            // a situação é a dupla — o documento distingue as três.
            return $grade < self::PASSING_GRADE
                ? EnrollmentStatus::FailedBoth
                : EnrollmentStatus::FailedAbsence;
        }

        return $grade >= self::requiredGrade($attendance)
            ? EnrollmentStatus::Approved
            : EnrollmentStatus::FailedGrade;
    }

    /** A média que ESTA frequência exige. É o número do alerta preventivo. */
    public static function requiredGrade(float $attendance): float
    {
        return $attendance < self::FULL_ATTENDANCE
            ? self::PASSING_GRADE_LOW_ATTENDANCE
            : self::PASSING_GRADE;
    }

    /**
     * Já é impossível aprovar por frequência? Com isso a interface avisa
     * ENQUANTO dá tempo, em vez de no fim do semestre.
     */
    public static function isLostByAttendance(float $attendance): bool
    {
        return $attendance < self::MIN_ATTENDANCE;
    }

    /**
     * A faixa que o RÓTULO do documento revela.
     *
     * Descoberta empírica, sem exceção nos 14 aprovados do histórico real:
     * "Aprovado Por Nota/Frequência" ⟺ frequência ≥ 75 %, e
     * "Aprovado Por Nota" ⟺ frequência < 75 %. Serve para inferir a faixa
     * quando o campo de frequência vem vazio ("*") no documento.
     *
     * @return array{0: float, 1: ?float}|null [mínimo, máximo] em %
     */
    public static function attendanceRangeFromLabel(string $printedLabel): ?array
    {
        $n = mb_strtolower($printedLabel);

        return match (true) {
            str_contains($n, 'nota/frequ') && str_contains($n, 'aprovado') => [self::FULL_ATTENDANCE, null],
            str_contains($n, 'aprovado por nota') => [self::MIN_ATTENDANCE, self::FULL_ATTENDANCE],
            default => null,
        };
    }
}
