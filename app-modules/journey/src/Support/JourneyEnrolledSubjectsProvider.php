<?php

declare(strict_types=1);

namespace CampusOs\Journey\Support;

use CampusOs\Core\Contracts\EnrolledSubjectsProvider;
use CampusOs\Journey\Models\SubjectEnrollment;

/**
 * A implementação real da pergunta do contrato: soma as matrículas de TODOS os
 * vínculos do usuário (um aluno pode ter mais de um `Registration`), sem
 * filtrar por termo — é essa ausência de filtro que faz o acervo atravessar
 * semestres.
 */
final class JourneyEnrolledSubjectsProvider implements EnrolledSubjectsProvider
{
    public function subjectIdsFor(string $userId): array
    {
        return SubjectEnrollment::query()
            ->whereHas('registration.student', fn ($q) => $q->where('user_usr_id', $userId))
            ->pluck('subject_sbj_id')
            ->unique()
            ->values()
            ->all();
    }
}
