<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Http\Resources;

use CampusOs\Catalog\Models\Curriculum;
use CampusOs\Catalog\Models\CurriculumSubject;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A matriz com as disciplinas agrupadas por período — o formato que a tela de
 * "grade do curso" consome inteiro, sem segunda chamada.
 *
 * @mixin Curriculum
 */
final class CurriculumResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cur_id,
            'code' => $this->cur_code,
            'name' => $this->cur_name,
            'version' => $this->cur_version,
            'status' => $this->cur_status->value,
            'effective_from' => $this->cur_effective_from?->toDateString(),

            // As três faixas que somam, mais a exigência ortogonal de extensão.
            // total_hours é CALCULADO pela fórmula do documento da matriz —
            // não existe coluna, e é de propósito.
            'workload' => [
                'mandatory_hours' => $this->cur_mandatory_hours,
                'elective_hours' => $this->cur_elective_hours,
                'standalone_extension_hours' => $this->cur_standalone_extension_hours,
                'total_hours' => $this->totalRequiredHours(),
                'extension_hours' => $this->cur_extension_hours,
                'extension_counts_in_total' => false,
            ],

            'rules' => [
                'expected_terms' => $this->cur_expected_terms,
                'max_terms' => $this->cur_max_terms,
                'max_term_hours' => $this->cur_max_term_hours,
                'max_weekly_deficit' => $this->cur_max_weekly_deficit,
            ],

            // B7: o teto por categoria de atividade complementar. Puramente
            // informativo — não entra em `workload` (ver
            // docs/dominio/HORAS_COMPLEMENTARES.md).
            'complementary_categories' => $this->whenLoaded('complementaryCategories', fn () => $this->complementaryCategories
                ->map(fn ($c): array => [
                    'id' => $c->ccg_id,
                    'name' => $c->ccg_name,
                    'max_hours' => $c->ccg_max_hours,
                    'conversion_note' => $c->ccg_conversion_note,
                ])->values()),

            'elective_groups' => $this->whenLoaded('electiveGroups', fn () => $this->electiveGroups
                ->map(fn ($g): array => [
                    'code' => $g->elg_code,
                    'name' => $g->elg_name,
                    'required_hours' => $g->elg_required_hours,
                    'weekly_hours' => (float) $g->elg_weekly_hours,
                    'first_term' => $g->elg_first_term,
                    'last_term' => $g->elg_last_term,
                    'weekly_hours_per_term' => $g->weeklyHoursPerTerm(),
                ])->values()),

            'terms' => $this->whenLoaded('curriculumSubjects', fn () => $this->curriculumSubjects
                ->sortBy(['cbs_term', fn (CurriculumSubject $cs) => $cs->subject->sbj_code])
                ->groupBy('cbs_term')
                ->map(fn ($group, $term): array => [
                    'term' => (int) $term,
                    'subjects' => $group->map(fn (CurriculumSubject $cs): array => [
                        'code' => $cs->subject->sbj_code,
                        'name' => $cs->subject->sbj_name,
                        'nature' => $cs->cbs_nature->value,
                        'model' => $cs->subject->sbj_model?->value,
                        'weekly_hours' => $cs->subject->sbj_weekly_hours,
                        'hours' => $cs->subject->sbj_hours,
                        'extension_hours' => $cs->subject->sbj_extension_hours,
                        'elective_group' => $cs->electiveGroup?->elg_code,
                        'prerequisites' => $cs->prerequisites
                            ->map(fn ($p): array => [
                                'type' => $p->prq_type->value,
                                'subject' => $p->required?->subject->sbj_code,
                                'min_term' => $p->prq_min_term,
                                'min_hours' => $p->prq_min_hours,
                            ])->values(),
                    ])->values(),
                ])->values()),
        ];
    }
}
