<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Concerns;

use CampusOs\Journey\Models\Registration;
use CampusOs\Tenancy\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * O mesmo lookup de `ProgressController`/`EligibilityController`/
 * `ComplementaryActivityController::resolveRegistration` — o vínculo mais
 * recente do usuário autenticado. Vive aqui (não num Trait do `journey`)
 * porque é só as tools MCP que precisam dele em mais de um lugar; os
 * controllers REST já tinham cada um a própria cópia antes deste canal
 * existir, e mexer neles está fora do escopo desta entrega.
 */
trait ResolvesRegistration
{
    private function resolveRegistration(User $user): Registration
    {
        $registration = Registration::query()
            ->whereRelation('student', 'user_usr_id', $user->usr_id)
            ->orderByDesc('reg_created_at')
            ->first();

        if ($registration === null) {
            throw ValidationException::withMessages([
                'registration' => 'Você ainda não tem vínculo acadêmico. Envie seu histórico escolar.',
            ]);
        }

        return $registration;
    }
}
