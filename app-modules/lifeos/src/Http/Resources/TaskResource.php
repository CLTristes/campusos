<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Http\Resources;

use CampusOs\Lifeos\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
final class TaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->tsk_id,
            'title' => $this->tsk_title,
            'description_md' => $this->tsk_description_md,
            'status' => $this->tsk_status->value,
            'status_label' => $this->tsk_status->label(),
            'kind' => $this->tsk_kind->value,
            'kind_label' => $this->tsk_kind->label(),
            'visibility' => $this->tsk_visibility->value,
            'visibility_label' => $this->tsk_visibility->label(),
            'due_at' => $this->tsk_due_at?->toIso8601String(),
            'subject_id' => $this->subject_sbj_id,
            'offering_id' => $this->offering_ofr_id,
            'origin_task_id' => $this->origin_tsk_id,
            'owner' => $this->whenLoaded('owner', fn (): array => [
                'id' => $this->owner->usr_id,
                'name' => $this->owner->usr_name,
            ]),
            'created_at' => $this->tsk_created_at?->toIso8601String(),
        ];
    }
}
