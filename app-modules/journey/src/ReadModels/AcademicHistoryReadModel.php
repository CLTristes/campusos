<?php

declare(strict_types=1);

namespace CampusOs\Journey\ReadModels;

use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\SubjectEnrollment;

/**
 * O histórico completo, disciplina por disciplina — nenhum outro read model
 * expõe isto hoje: `ProgressReadModel`/`ComplementaryHoursReadModel` só
 * agregam, `EligibilityReadModel` só olha pendente. Puro dado, nenhum `if` de
 * negócio: a pergunta "isto conta?" já mora em `EnrollmentStatus`.
 */
final class AcademicHistoryReadModel
{
    /** @return list<array<string, mixed>> */
    public function forRegistration(Registration $registration): array
    {
        return $registration->subjectEnrollments()
            ->with(['subject', 'term'])
            ->get()
            ->sortBy(fn (SubjectEnrollment $e): string => sprintf(
                '%04d-%d-%s',
                $e->term->trm_year,
                $e->term->trm_period,
                $e->subject->sbj_code,
            ))
            ->map(fn (SubjectEnrollment $e): array => [
                'subject' => ['code' => $e->subject->sbj_code, 'name' => $e->subject->sbj_name],
                'term' => ['year' => $e->term->trm_year, 'period' => $e->term->trm_period],
                'status' => $e->sen_status->value,
                'status_label' => $e->sen_status->label(),
                'counts_as_completed' => $e->sen_status->countsAsCompleted(),
                'grade' => $e->sen_grade,
                'attendance' => $e->sen_attendance,
                'hours_earned' => $e->sen_hours_earned,
                'class_code' => $e->sen_class_code,
            ])
            ->values()
            ->all();
    }
}
