<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Http\Resources;

use CampusOs\Tenancy\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Projeção mínima do usuário. O hash da senha e o remember_token não aparecem
 * aqui nem por acidente — estão no $hidden do model E fora deste array, e o
 * teste cobre os dois (regra de ouro nº 6).
 *
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->usr_id,
            'name' => $this->usr_name,
            'email' => $this->usr_email,
            'role' => $this->usr_role->value,
            'role_label' => $this->usr_role->label(),
            'registration_number' => $this->usr_registration_number,
            'email_verified' => $this->usr_email_verified_at !== null,
            'entity_id' => $this->entity_ent_id,
            'campus' => $this->whenLoaded('campus', fn (): ?array => $this->campus === null ? null : [
                'id' => $this->campus->cps_id,
                'code' => $this->campus->cps_code,
                'name' => $this->campus->cps_name,
            ]),
            'enabled_modules' => $this->usr_enabled_modules ?? [],
        ];
    }
}
