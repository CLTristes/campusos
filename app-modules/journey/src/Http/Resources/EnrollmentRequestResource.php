<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Resources;

use CampusOs\Journey\Models\EnrollmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * O que a tela de conferência consome. `erq_file_path` NUNCA sai daqui — é o
 * caminho de um arquivo pessoal no disco do servidor.
 *
 * @mixin EnrollmentRequest
 */
final class EnrollmentRequestResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->erq_id,
            'kind' => $this->erq_kind,
            'status' => $this->erq_status->value,
            'status_label' => $this->erq_status->label(),
            'is_pending' => $this->erq_status->isPending(),
            'confidence' => $this->erq_confidence === null ? null : (float) $this->erq_confidence,
            'provider' => $this->erq_provider,
            'failure_reason' => $this->erq_failure_reason,
            'extraction' => $this->erq_extraction,
            'parsed_at' => $this->erq_parsed_at?->toIso8601String(),
            'confirmed_at' => $this->erq_confirmed_at?->toIso8601String(),
        ];
    }
}
