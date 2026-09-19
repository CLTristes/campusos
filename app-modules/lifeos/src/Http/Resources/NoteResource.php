<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Http\Resources;

use CampusOs\Lifeos\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Autoria é sempre visível (Fase 5 regra 4 do desenho — o acervo anônimo não
 * cria pertencimento). O que NUNCA aparece aqui é nota de prova ou situação de
 * matrícula: `Note` não tem relação nenhuma com `subject_enrollments.sen_grade`
 * ou `sen_status`, então não há campo pra vazar por acidente.
 *
 * @mixin Note
 */
final class NoteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->nte_id,
            'title' => $this->nte_title,
            'body_md' => $this->nte_body_md,
            'kind' => $this->nte_kind->value,
            'kind_label' => $this->nte_kind->label(),
            'visibility' => $this->nte_visibility->value,
            'visibility_label' => $this->nte_visibility->label(),
            'author' => $this->whenLoaded('author', fn (): array => [
                'id' => $this->author->usr_id,
                'name' => $this->author->usr_name,
            ]),
            'subject_id' => $this->subject_sbj_id,
            'offering_id' => $this->offering_ofr_id,
            'course_id' => $this->course_crs_id,
            'term' => $this->whenLoaded('term', fn (): ?array => $this->term === null ? null : [
                'year' => $this->term->trm_year,
                'period' => $this->term->trm_period,
            ]),
            'published_at' => $this->nte_published_at?->toIso8601String(),
            'created_at' => $this->nte_created_at?->toIso8601String(),
        ];
    }
}
