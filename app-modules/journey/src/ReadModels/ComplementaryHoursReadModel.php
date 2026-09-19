<?php

declare(strict_types=1);

namespace CampusOs\Journey\ReadModels;

use CampusOs\Journey\Models\ComplementaryActivity;
use CampusOs\Journey\Models\Registration;
use Illuminate\Support\Collection;

/**
 * O contador por categoria (B7) — por categoria: min(SUM(horas declaradas
 * que ainda contam), teto da categoria). O total é a soma dos MÍNIMOS, nunca
 * a soma bruta declarada.
 *
 * Puramente informativo: NÃO alimenta `ProgressReadModel` (decisão do dono
 * do produto, ver docs/dominio/PROGRESSAO.md pendência 3 e
 * docs/dominio/HORAS_COMPLEMENTARES.md). `ATV001`, quando aparecer no
 * histórico oficial, é o que de fato conta como aprovado.
 *
 * Armadilha evitada de propósito: dividir a perda entre CERTIFICADOS (linhas)
 * é ambíguo quando o teto corta no meio de um conjunto. O corte é sempre
 * calculado agregado POR CATEGORIA — nunca por linha.
 */
final class ComplementaryHoursReadModel
{
    /** @return Collection<int, array<string, mixed>> uma entrada por categoria com atividade */
    public function summaryFor(Registration $registration): Collection
    {
        return $this->activitiesByCategory($registration)
            ->map(fn (Collection $activities) => $this->summarizeCategory($activities))
            ->values();
    }

    /** O resumo de UMA categoria — usado pra devolver o "capped"/"hours_not_counted" na criação. */
    public function summaryForCategory(Registration $registration, string $categoryId): ?array
    {
        return $this->activitiesByCategory($registration)
            ->get($categoryId)
            ?->pipe(fn (Collection $activities) => $this->summarizeCategory($activities));
    }

    /** @return Collection<string, Collection<int, ComplementaryActivity>> */
    private function activitiesByCategory(Registration $registration): Collection
    {
        return $registration->complementaryActivities()
            ->with('category')
            ->get()
            ->filter(fn (ComplementaryActivity $a): bool => $a->cac_status->countsTowardHours())
            ->groupBy('complementary_category_ccg_id');
    }

    /** @param  Collection<int, ComplementaryActivity>  $activities @return array<string, mixed> */
    private function summarizeCategory(Collection $activities): array
    {
        $category = $activities->first()->category;
        $claimed = (int) $activities->sum('cac_hours_claimed');
        $granted = min($claimed, $category->ccg_max_hours);

        return [
            'category' => [
                'id' => $category->ccg_id,
                'name' => $category->ccg_name,
                'max_hours' => $category->ccg_max_hours,
            ],
            'hours_claimed' => $claimed,
            'hours_granted' => $granted,
            'hours_not_counted' => $claimed - $granted,
            'capped' => $claimed > $granted,
        ];
    }
}
