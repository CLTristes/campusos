<?php

declare(strict_types=1);

namespace CampusOs\Journey\ReadModels;

use CampusOs\Journey\DTOs\ProgressTrack;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\Models\SubjectEnrollment;
use Illuminate\Support\Collection;

/**
 * A progressão da graduação — CALCULADA, nunca armazenada.
 *
 * Um percentual gravado em coluna fica velho no instante em que uma nota muda,
 * e ninguém percebe. Custo: 3 queries somadas em memória; numa graduação são
 * ~60 linhas de histórico, então otimizar isso agora seria otimizar o nada.
 *
 * Regras em docs/dominio/PROGRESSAO.md §7 a §11.
 */
final class ProgressReadModel
{
    /** @return array<string, mixed> */
    public function for(Registration $registration): array
    {
        $registration->loadMissing(['curriculum', 'course', 'entryTerm']);
        $curriculum = $registration->curriculum;

        $enrollments = $registration->subjectEnrollments()
            ->with(['subject', 'term'])
            ->get();

        $completed = $enrollments->filter(fn (SubjectEnrollment $e): bool => $e->sen_status->countsAsCompleted());
        $inProgress = $enrollments->filter(fn (SubjectEnrollment $e): bool => $e->sen_status->isInProgress());

        // A natureza (obrigatória × optativa) é da MATRIZ, não da disciplina —
        // por isso vem de curriculum_subjects e não de subjects.
        $natureBySubject = $curriculum->curriculumSubjects()
            ->pluck('cbs_nature', 'subject_sbj_id');

        $hoursBy = fn (Collection $set, string $nature): int => (int) $set
            ->filter(fn (SubjectEnrollment $e): bool => ($natureBySubject[$e->subject_sbj_id] ?? null)?->value === $nature)
            ->sum(fn (SubjectEnrollment $e): int => $e->sen_hours_earned ?: (int) $e->subject->sbj_hours);

        // Extensão: ortogonal. Soma a parcela CHEXT embutida nas disciplinas já
        // cumpridas — NUNCA entra no total do curso (regra 7).
        $extensionDone = (int) $completed->sum(fn (SubjectEnrollment $e): int => (int) $e->subject->sbj_extension_hours);

        $tracks = [
            new ProgressTrack(
                'mandatory', 'Obrigatórias',
                $hoursBy($completed, 'mandatory'),
                $curriculum->cur_mandatory_hours,
                $hoursBy($inProgress, 'mandatory'),
            ),
            new ProgressTrack(
                'elective', 'Optativas',
                $hoursBy($completed, 'elective'),
                $curriculum->cur_elective_hours,
                $hoursBy($inProgress, 'elective'),
            ),
            // A parcela de extensão que NÃO cabe dentro de disciplina e por isso
            // soma. Cumpre-se por Componente Curricular Extensionista autônomo,
            // que ainda não é modelado — ver ressalvas do report.
            new ProgressTrack(
                'standalone_extension', 'Extensão autônoma (CCE)',
                0,
                $curriculum->cur_standalone_extension_hours,
            ),
        ];

        $countedTotal = array_sum(array_map(fn (ProgressTrack $t): int => $t->counted(), $tracks));
        $requiredTotal = $curriculum->totalRequiredHours();

        return [
            'registration' => [
                'id' => $registration->reg_id,
                'number' => $registration->reg_number,
                'status' => $registration->reg_status->value,
                'status_label' => $registration->reg_status->label(),
                'course' => $registration->course->crs_name,
                'curriculum' => $registration->curriculum->cur_code,
                'entry_term' => $registration->entryTerm->label(),
            ],
            'overall' => [
                'completed_hours' => $countedTotal,
                'required_hours' => $requiredTotal,
                'in_progress_hours' => array_sum(array_map(fn (ProgressTrack $t): int => $t->inProgress, $tracks)),
                'percentage' => $requiredTotal === 0 ? 0.0 : round(($countedTotal / $requiredTotal) * 100, 1),
            ],
            'tracks' => array_map(fn (ProgressTrack $t): array => $t->toArray(), $tracks),
            'extension' => [
                'completed' => $extensionDone,
                'required' => $curriculum->cur_extension_hours,
                'remaining' => max(0, $curriculum->cur_extension_hours - $extensionDone),
                // A distinção que o produto inteiro depende de acertar.
                'counts_in_total' => false,
            ],
            'pending_subjects' => $this->pending($registration, $completed, $inProgress),
            'forecast' => $this->forecast($registration, $enrollments, $requiredTotal - $countedTotal),
        ];
    }

    /**
     * O que falta, agrupado pelo período sugerido da matriz.
     *
     * @param  Collection<int, SubjectEnrollment>  $completed
     * @param  Collection<int, SubjectEnrollment>  $inProgress
     * @return list<array<string, mixed>>
     */
    private function pending(Registration $registration, Collection $completed, Collection $inProgress): array
    {
        $done = $completed->pluck('subject_sbj_id')->all();
        $doing = $inProgress->pluck('subject_sbj_id')->all();

        return $registration->curriculum->curriculumSubjects()
            ->with('subject')
            ->whereNotIn('subject_sbj_id', $done)
            ->orderBy('cbs_term')
            ->get()
            ->map(fn ($cs): array => [
                'code' => $cs->subject->sbj_code,
                'name' => $cs->subject->sbj_name,
                'term' => $cs->cbs_term,
                'hours' => $cs->subject->sbj_hours,
                'weekly_hours' => $cs->subject->sbj_weekly_hours,
                'extension_hours' => $cs->subject->sbj_extension_hours,
                'nature' => $cs->cbs_nature->value,
                'in_progress' => in_array($cs->subject_sbj_id, $doing, true),
            ])
            ->values()
            ->all();
    }

    /**
     * Previsão pelo ritmo REAL do aluno, não pelo ideal da matriz (regra 11).
     * O ideal engana; o real é o que a pessoa precisa ouvir.
     *
     * @param  Collection<int, SubjectEnrollment>  $enrollments
     * @return array<string, mixed>
     */
    private function forecast(Registration $registration, Collection $enrollments, int $remainingHours): array
    {
        $termsAttended = $enrollments->pluck('term_trm_id')->unique()->count();
        $approvedHours = $enrollments
            ->filter(fn (SubjectEnrollment $e): bool => $e->sen_status->countsAsCompleted())
            ->sum(fn (SubjectEnrollment $e): int => $e->sen_hours_earned ?: (int) $e->subject->sbj_hours);

        $pace = $termsAttended > 0 ? (int) round($approvedHours / $termsAttended) : 0;
        $remainingTerms = $pace > 0 ? (int) ceil($remainingHours / $pace) : null;

        $maxTerms = $registration->curriculum->cur_max_terms;
        $termsUntilDeadline = $maxTerms === null ? null : max(0, $maxTerms - $termsAttended);

        return [
            'terms_attended' => $termsAttended,
            'avg_hours_per_term' => $pace,
            'remaining_hours' => max(0, $remainingHours),
            'remaining_terms' => $remainingTerms,
            'terms_until_deadline' => $termsUntilDeadline,
            // Em risco quando o ritmo real não cabe no prazo que resta.
            'at_risk' => $remainingTerms !== null
                && $termsUntilDeadline !== null
                && $remainingTerms > $termsUntilDeadline,
            'failures' => $enrollments->filter(fn (SubjectEnrollment $e): bool => $e->sen_status->isFailure())->count(),
        ];
    }
}
