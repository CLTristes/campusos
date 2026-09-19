<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Tenancy\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Autenticação por e-mail e senha, devolvendo um token Sanctum.
 *
 * O e-mail é único POR INSTITUIÇÃO, não globalmente — duas universidades podem
 * ter o mesmo endereço cadastrado. Por isso o login roda FORA do escopo de
 * tenant (`withoutScope`): neste momento ainda não existe tenant nenhum no
 * contexto, e é justamente do usuário encontrado que ele sai.
 *
 * Se o mesmo e-mail existir em mais de uma instituição, o login é ambíguo e
 * responde pedindo a instituição — em vez de escolher uma por conta própria.
 */
final class LoginAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'entity_id' => ['nullable', 'string', 'uuid'],
            'device' => ['nullable', 'string', 'max:64'],
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

    /** @param array<string, mixed> $data */
    protected function handle(array $data): array
    {
        /** @var list<User> $candidates */
        $candidates = TenantContext::withoutScope(
            fn (): array => User::query()
                ->where('usr_email', $data['email'])
                ->when(
                    $data['entity_id'] ?? null,
                    fn ($q, $id) => $q->where('entity_ent_id', $id),
                )
                ->get()
                ->all(),
        );

        if (count($candidates) > 1) {
            throw ValidationException::withMessages([
                'entity_id' => 'Este e-mail está cadastrado em mais de uma instituição. Informe qual.',
            ]);
        }

        $user = $candidates[0] ?? null;

        // Mensagem idêntica para e-mail inexistente e senha errada: dizer qual
        // dos dois falhou entrega a um atacante a lista de quem tem conta.
        if ($user === null || ! Hash::check((string) $data['password'], $user->usr_password)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas.',
            ]);
        }

        // A partir daqui o tenant existe — todo o resto da request opera nele.
        TenantContext::set($user->entity_ent_id);

        return [
            'user' => $user,
            'token' => $user->createToken((string) $data['device'])->plainTextToken,
        ];
    }
}
