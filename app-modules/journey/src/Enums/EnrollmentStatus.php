<?php

declare(strict_types=1);

namespace CampusOs\Journey\Enums;

/**
 * As dez situações que o histórico escolar da UTFPR imprime — nenhuma
 * inventada (ver docs/dominio/DOCUMENTOS_ACADEMICOS.md §1.3).
 *
 * A pergunta "isto conta para a integralização?" mora AQUI, num lugar só.
 * Três cópias levemente diferentes dela é como um sistema acadêmico passa a
 * mostrar três números diferentes para a mesma pergunta.
 */
enum EnrollmentStatus: string
{
    case Enrolled = 'enrolled';
    case Approved = 'approved';
    case ApprovedExam = 'approved_exam';
    case Credited = 'credited';
    case Exempted = 'exempted';
    case FailedGrade = 'failed_grade';
    case FailedAbsence = 'failed_absence';
    case FailedBoth = 'failed_both';
    case Withdrawn = 'withdrawn';
    case Cancelled = 'cancelled';

    /** Conta para a integralização? (docs/dominio/PROGRESSAO.md regra 4) */
    public function countsAsCompleted(): bool
    {
        return in_array($this, [
            self::Approved,
            self::ApprovedExam,
            self::Credited,
            self::Exempted,
        ], true);
    }

    /** Carga em andamento — nunca somada ao cumprido. */
    public function isInProgress(): bool
    {
        return $this === self::Enrolled;
    }

    /** Impede que a disciplina destrave quem depende dela. */
    public function blocksPrerequisiteChain(): bool
    {
        return ! $this->countsAsCompleted();
    }

    /** Reprovação de fato (contra trancamento/cancelamento, que não reprovam). */
    public function isFailure(): bool
    {
        return in_array($this, [self::FailedGrade, self::FailedAbsence, self::FailedBoth], true);
    }

    /**
     * Texto exatamente como o documento imprime — usado pelo extrator para
     * casar a situação lida, e pela interface para não inventar vocabulário
     * novo para quem já conhece o do portal.
     *
     * @return list<string>
     */
    public function documentLabels(): array
    {
        return match ($this) {
            self::Enrolled => ['Cursando'],
            self::Approved => ['Aprovado Por Nota/Frequência', 'Aprovado Por Nota'],
            self::ApprovedExam => ['Aprovado Em Exame De Suficiência'],
            self::Credited => ['Crédito Consignado'],
            self::Exempted => ['Dispensa', 'Enade - Estudante Dispensado'],
            self::FailedGrade => ['Reprovado Por Nota'],
            self::FailedAbsence => ['Reprovado Por Frequência'],
            self::FailedBoth => ['Reprovado Por Nota/Frequência'],
            self::Withdrawn => ['Trancado'],
            self::Cancelled => ['Cancelado'],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Enrolled => 'Cursando',
            self::Approved => 'Aprovado',
            self::ApprovedExam => 'Aprovado em exame de suficiência',
            self::Credited => 'Crédito consignado',
            self::Exempted => 'Dispensado',
            self::FailedGrade => 'Reprovado por nota',
            self::FailedAbsence => 'Reprovado por frequência',
            self::FailedBoth => 'Reprovado por nota e frequência',
            self::Withdrawn => 'Trancado',
            self::Cancelled => 'Cancelado',
        };
    }
}
