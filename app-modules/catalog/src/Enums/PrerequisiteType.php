<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

/**
 * Dois tipos observados na matriz 45; dois previstos.
 *
 * `MinimumTerm` existe porque EST501 (Estágio Curricular Obrigatório) exige
 * literalmente "Período: 5" — não uma disciplina. É o motivo de
 * prerequisites.required_cbs_id ser nullable.
 */
enum PrerequisiteType: string
{
    case Subject = 'subject';
    case MinimumTerm = 'minimum_term';
    case Corequisite = 'corequisite';
    case MinimumHours = 'minimum_hours';

    /** Aponta para outra disciplina da matriz? */
    public function targetsSubject(): bool
    {
        return in_array($this, [self::Subject, self::Corequisite], true);
    }
}
