<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Http\Resources;

use CampusOs\Catalog\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Course */
final class CourseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->crs_id,
            'code' => $this->crs_code,
            'name' => $this->crs_name,
            'degree' => $this->crs_degree->value,
            'degree_label' => $this->crs_degree->label(),
            'shift' => $this->crs_shift?->value,
            'shift_label' => $this->crs_shift?->label(),
            'campus' => $this->whenLoaded('campus', fn (): array => [
                'id' => $this->campus->cps_id,
                'code' => $this->campus->cps_code,
                'name' => $this->campus->cps_name,
            ]),
        ];
    }
}
