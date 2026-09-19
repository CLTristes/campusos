<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Tenancy\Mail\VerificationCodeMail;
use CampusOs\Tenancy\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/** Gera um código novo — o de 15 minutos atrás pode ter expirado. */
final class ResendVerificationCodeAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return ['user_id' => ['required', 'string', 'uuid']];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): null
    {
        $user = User::query()->findOrFail($data['user_id']);

        if ($user->usr_email_verified_at !== null) {
            throw ValidationException::withMessages([
                'code' => 'Este e-mail já foi verificado.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        $user->update([
            'usr_verification_code' => $code,
            'usr_verification_code_expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->usr_email)->send(new VerificationCodeMail($code));

        return null;
    }
}
