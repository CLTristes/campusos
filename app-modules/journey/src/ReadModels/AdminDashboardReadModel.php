<?php

declare(strict_types=1);

namespace CampusOs\Journey\ReadModels;

use CampusOs\Journey\Models\EnrollmentRequest;
use CampusOs\Journey\Models\Registration;
use Illuminate\Support\Collection;

/**
 * O painel administrativo NOMEADO — diferente de `insights`
 * (`AcademicStatsProvider`), que é agregado anônimo por desenho
 * (`MIN_COHORT=5`, "nunca aluno nomeado", ver `PAINEL_COORDENACAO.md`). Este
 * read model é para quem JÁ tem acesso a dado nomeado — o mesmo
 * `/data-console` do Filament já mostra `Student`/`Registration` crus — só
 * que agregado, pra caber num dashboard em vez de tabela por tabela.
 *
 * `courseId: null` é a instituição inteira (`institution_admin`);
 * um valor filtra pro curso do coordenador — mesmo `resolveCourseId()` de
 * `StaffInsightsController`.
 */
final class AdminDashboardReadModel
{
    /** @return array<string, mixed> */
    public function summary(?string $courseId): array
    {
        $registrations = Registration::query()
            ->with('course')
            ->when($courseId !== null, fn ($q) => $q->where('course_crs_id', $courseId))
            ->get();

        return [
            'total_students' => $this->totalStudents($registrations),
            'total_registrations' => $registrations->count(),
            'students_by_course' => $this->byCourse($registrations),
            'document_requests_by_status' => $this->documentRequestsByStatus($courseId),
            'average_progress_percentage' => $this->averageProgressPercentage($registrations),
        ];
    }

    /** Um Student pode ter dois vínculos (um por curso) — conta pessoas, não vínculos. */
    private function totalStudents(Collection $registrations): int
    {
        return $registrations->pluck('student_std_id')->unique()->count();
    }

    /** @return list<array<string, mixed>> */
    private function byCourse(Collection $registrations): array
    {
        return $registrations
            ->groupBy(fn (Registration $r): string => $r->course?->crs_name ?? 'Sem curso')
            ->map(fn (Collection $group, string $name): array => ['course' => $name, 'count' => $group->count()])
            ->values()
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * `uploaded`/`processing`/`parsed` — ainda não virou matrícula. Escopado
     * por curso via `whereHas` na registration, já que o documento em si não
     * tem curso até a confirmação resolver o vínculo.
     *
     * @return list<array<string, mixed>>
     */
    private function documentRequestsByStatus(?string $courseId): array
    {
        return EnrollmentRequest::query()
            ->when($courseId !== null, fn ($q) => $q->whereHas('registration', fn ($r) => $r->where('course_crs_id', $courseId)))
            ->selectRaw('erq_status, COUNT(*) as total')
            ->groupBy('erq_status')
            ->get()
            ->map(fn ($row): array => [
                'status' => $row->erq_status->value,
                'status_label' => $row->erq_status->label(),
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    /**
     * Custo real: uma query de progresso por vínculo. Numa base de dezenas de
     * alunos (o piloto) isso é barato; otimizar agora seria otimizar o nada
     * — mesma ressalva de `ProgressReadModel`.
     */
    private function averageProgressPercentage(Collection $registrations): ?float
    {
        if ($registrations->isEmpty()) {
            return null;
        }

        $percentages = $registrations->map(
            fn (Registration $r): float => (float) (new ProgressReadModel)->for($r)['overall']['percentage']
        );

        return round((float) $percentages->avg(), 1);
    }
}
