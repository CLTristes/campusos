<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Resources;

use CampusOs\Journey\Models\ComplementaryActivity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ComplementaryActivity
 */
final class ComplementaryActivityResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->cac_id,
            'title' => $this->cac_title,
            'hours_claimed' => $this->cac_hours_claimed,
            'hours_granted' => $this->cac_hours_granted,
            'issued_at' => $this->cac_issued_at?->toDateString(),
            'status' => $this->cac_status->value,
            'status_label' => $this->cac_status->label(),
            'has_certificate' => $this->cac_certificate_path !== null,
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->category->ccg_id,
                'name' => $this->category->ccg_name,
                'max_hours' => $this->category->ccg_max_hours,
            ]),
            'created_at' => $this->cac_created_at?->toIso8601String(),
        ];
    }
}
