<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Mail\VerificationCodeMail;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Cadastro livre do aluno — sem formulário de "escolha sua universidade".
 *
 * A instituição é resolvida pelo DOMÍNIO do e-mail (`ent_email_domain`), não
 * escolhida pelo cliente: é a mesma postura do login sobre a matriz em
 * `CreateRegistrationAction` — quem prova pertencimento é o servidor, nunca o
 * request. Sem instituição com aquele domínio configurado, o cadastro livre
 * simplesmente não existe para aquele endereço (aluno normal não erra o
 * próprio e-mail; quem erra é golpe ou teste).
 *
 * Acesso é IMEDIATO — o token sai deste request. A verificação por e-mail é
 * uma camada de segurança em paralelo (prova que a caixa de entrada é do
 * dono), não um portão: o aluno já usa o sistema e resolve o histórico depois.
 */
final class SignupAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device' => ['nullable', 'string', 'max:64'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'password.confirmed' => 'A confirmação de senha não bate.',
        ];
    }

    /** @param array<string, mixed> $input @return array<string, mixed> */
    protected function sanitize(array $input): array
    {
        return [
            ...$input,
            'email' => mb_strtolower(trim((string) ($input['email'] ?? ''))),
            'device' => trim((string) ($input['device'] ?? 'api')) ?: 'api',
        ];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    protected function handle(array $data): array
    {
        $domain = Str::after($data['email'], '@');

        $entity = TenantContext::withoutScope(
            fn (): ?Entity => Entity::query()->where('ent_email_domain', $domain)->first(),
        );

        if ($entity === null) {
            throw ValidationException::withMessages([
                'email' => 'Use seu e-mail institucional — este domínio não está habilitado para cadastro.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        $user = TenantContext::runAs($entity->ent_id, function () use ($data, $code): User {
            if (User::query()->where('usr_email', $data['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Já existe uma conta com este e-mail nesta instituição.',
                ]);
            }

            return User::query()->create([
                'usr_name' => $data['name'],
                'usr_email' => $data['email'],
                'usr_password' => $data['password'],
                'usr_role' => UserRole::Student,
                'usr_verification_code' => $code,
                'usr_verification_code_expires_at' => now()->addMinutes(15),
            ]);
        });

        Mail::to($user->usr_email)->send(new VerificationCodeMail($code));

        // A partir daqui o tenant existe — mesma postura do LoginAction.
        TenantContext::set($entity->ent_id);

        return [
            'user' => $user,
            'token' => $user->createToken((string) $data['device'])->plainTextToken,
        ];
    }
}
