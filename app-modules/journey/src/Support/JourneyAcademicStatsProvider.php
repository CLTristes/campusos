<?php

declare(strict_types=1);

namespace CampusOs\Journey\Support;

use CampusOs\Core\Contracts\AcademicStatsProvider;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Journey\Enums\RegistrationStatus;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\ReadModels\EligibilityReadModel;
use CampusOs\Journey\ReadModels\ProgressReadModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A implementação real das quatro perguntas do painel — ver o contrato em
 * `AcademicStatsProvider` e as regras em
 * docs/dominio/PAINEL_COORDENACAO.md.
 *
 * O piso de anonimato (`MIN_COHORT`) é aplicado AQUI, no read model — nunca
 * escondido só na interface (esconder no front é vazamento com passo extra,
 * o JSON continua lá).
 *
 * **`DB::table()` não aplica `EntityScope`** — só Eloquent aplica global
 * scope. Toda query de agregação aqui filtra `entity_ent_id` explicitamente
 * por `TenantContext::id()`; esquecer isso vazaria a instituição inteira
 * quando `$courseId` for `null` (o escopo de `institution_admin`).
 */
final class JourneyAcademicStatsProvider implements AcademicStatsProvider
{
    private const MIN_COHORT = 5;

    public function failureRateBySubject(?string $courseId, int $lastTerms): array
    {
        $entityId = TenantContext::id();

        $termIds = DB::table('terms')
            ->where('terms.entity_ent_id', $entityId)
            ->whereIn('trm_id', function ($query) use ($courseId, $entityId): void {
                $query->select('subject_enrollments.term_trm_id')
                    ->from('subject_enrollments')
                    ->join('registrations', 'registrations.reg_id', '=', 'subject_enrollments.registration_reg_id')
                    ->where('registrations.entity_ent_id', $entityId)
                    ->when($courseId !== null, fn ($q) => $q->where('registrations.course_crs_id', $courseId));
            })
            ->orderByDesc('trm_year')
            ->orderByDesc('trm_period')
            ->limit($lastTerms)
            ->pluck('trm_id');

        // Denominador: tentativas com desfecho REAL (aprovado ou reprovado) —
        // crédito consignado e dispensa não são "tentativa" pra efeito de
        // reprovação, e matrícula em curso ainda não tem desfecho.
        $resolvedStatuses = ['approved', 'approved_exam', 'failed_grade', 'failed_absence', 'failed_both'];

        $rows = DB::table('subject_enrollments')
            ->join('subjects', 'subjects.sbj_id', '=', 'subject_enrollments.subject_sbj_id')
            ->join('registrations', 'registrations.reg_id', '=', 'subject_enrollments.registration_reg_id')
            ->where('registrations.entity_ent_id', $entityId)
            ->whereIn('subject_enrollments.term_trm_id', $termIds)
            ->whereIn('subject_enrollments.sen_status', $resolvedStatuses)
            ->when($courseId !== null, fn ($q) => $q->where('registrations.course_crs_id', $courseId))
            ->groupBy('subjects.sbj_id', 'subjects.sbj_code', 'subjects.sbj_name')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_COHORT])
            ->select([
                'subjects.sbj_code as subject_code',
                'subjects.sbj_name as subject_name',
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw("SUM(CASE WHEN subject_enrollments.sen_status = 'failed_grade' THEN 1 ELSE 0 END) as failed_grade"),
                DB::raw("SUM(CASE WHEN subject_enrollments.sen_status = 'failed_absence' THEN 1 ELSE 0 END) as failed_absence"),
                DB::raw("SUM(CASE WHEN subject_enrollments.sen_status = 'failed_both' THEN 1 ELSE 0 END) as failed_both"),
            ])
            ->get();

        return $rows
            ->map(function (object $row): array {
                $failed = (int) $row->failed_grade + (int) $row->failed_absence + (int) $row->failed_both;

                return [
                    'subject_code' => $row->subject_code,
                    'subject_name' => $row->subject_name,
                    'total_attempts' => (int) $row->total_attempts,
                    'failed_grade' => (int) $row->failed_grade,
                    'failed_absence' => (int) $row->failed_absence,
                    'failed_both' => (int) $row->failed_both,
                    'failure_rate' => round($failed / (int) $row->total_attempts, 4),
                ];
            })
            ->sortByDesc('failure_rate')
            ->values()
            ->all();
    }

    public function cohortDelay(?string $courseId): array
    {
        // COUNT DISTINCT por matrícula fica em SQL (subquery correlacionada);
        // só o agrupamento POR COORTE (poucas dezenas de linhas, uma por
        // vínculo ativo) vira PHP — nunca 40 mil linhas de subject_enrollments
        // trazidas pra somar aqui.
        $rows = DB::table('registrations')
            ->join('curricula', 'curricula.cur_id', '=', 'registrations.curriculum_cur_id')
            ->join('terms', 'terms.trm_id', '=', 'registrations.entry_term_trm_id')
            ->where('registrations.entity_ent_id', TenantContext::id())
            ->where('registrations.reg_status', RegistrationStatus::Active->value)
            ->when($courseId !== null, fn ($q) => $q->where('registrations.course_crs_id', $courseId))
            ->select([
                'terms.trm_year',
                'terms.trm_period',
                'curricula.cur_expected_terms',
                DB::raw('(SELECT COUNT(DISTINCT se.term_trm_id) FROM subject_enrollments se WHERE se.registration_reg_id = registrations.reg_id) as terms_attended'),
            ])
            ->get();

        $cohorts = $rows->groupBy(fn (object $row): string => "{$row->trm_year}/{$row->trm_period}");

        return $cohorts
            ->map(function ($registrations, string $entryTerm): array {
                $delays = $registrations->map(fn (object $r): int => max(0, (int) $r->terms_attended - (int) $r->cur_expected_terms));

                return [
                    'entry_term' => $entryTerm,
                    'active_registrations' => $registrations->count(),
                    'avg_delay_terms' => round((float) $delays->avg(), 2),
                ];
            })
            ->filter(fn (array $cohort): bool => $cohort['active_registrations'] >= self::MIN_COHORT)
            ->sortByDesc('avg_delay_terms')
            ->values()
            ->all();
    }

    public function registrationsNearDeadline(?string $courseId, int $within): int
    {
        $progress = new ProgressReadModel;

        return $this->activeRegistrations($courseId)
            ->filter(function (Registration $registration) use ($progress, $within): bool {
                $forecast = $progress->for($registration)['forecast'];

                return $forecast['terms_until_deadline'] !== null && $forecast['terms_until_deadline'] <= $within;
            })
            ->count();
    }

    public function demandForNextTerm(?string $courseId): array
    {
        $eligibility = new EligibilityReadModel;
        $demand = [];

        foreach ($this->activeRegistrations($courseId) as $registration) {
            foreach ($eligibility->nextTerm($registration)['eligible'] as $subject) {
                $demand[$subject['code']] ??= ['subject_code' => $subject['code'], 'subject_name' => $subject['name'], 'demand' => 0];
                $demand[$subject['code']]['demand']++;
            }
        }

        $rows = array_values(array_filter($demand, fn (array $row): bool => $row['demand'] >= self::MIN_COHORT));
        usort($rows, fn (array $a, array $b): int => $b['demand'] <=> $a['demand']);

        return $rows;
    }

    /** @return Collection<int, Registration> */
    private function activeRegistrations(?string $courseId): Collection
    {
        return Registration::query()
            ->where('reg_status', RegistrationStatus::Active->value)
            ->when($courseId !== null, fn ($q) => $q->where('course_crs_id', $courseId))
            ->get();
    }
}
