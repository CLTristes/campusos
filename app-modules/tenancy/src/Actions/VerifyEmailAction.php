<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Tenancy\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Confirma o código de 6 dígitos enviado no sign-up.
 *
 * NÃO bloqueia acesso — o aluno já está logado desde o cadastro. É só a marca
 * de que a caixa de entrada é dele mesmo, guardada para quando o produto
 * precisar dela (recuperação de senha, por exemplo).
 */
final class VerifyEmailAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'code' => ['required', 'string'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): User
    {
        $user = User::query()->findOrFail($data['user_id']);

        if ($user->usr_email_verified_at !== null) {
            throw ValidationException::withMessages([
                'code' => 'Este e-mail já foi verificado.',
            ]);
        }

        if ($user->usr_verification_code === null
            || $user->usr_verification_code_expires_at === null
            || $user->usr_verification_code_expires_at->isPast()
        ) {
            throw ValidationException::withMessages([
                'code' => 'Código expirado ou inexistente. Peça um novo.',
            ]);
        }

        if (! Hash::check($data['code'], $user->usr_verification_code)) {
            throw ValidationException::withMessages([
                'code' => 'Código inválido.',
            ]);
        }

        $user->update([
            'usr_email_verified_at' => now(),
            'usr_verification_code' => null,
            'usr_verification_code_expires_at' => null,
        ]);

        return $user;
    }
}
