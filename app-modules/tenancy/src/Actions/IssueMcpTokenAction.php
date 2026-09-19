<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Tenancy\Models\User;

/**
 * O token do copiloto (B8, terceira esticada) — separado do token de login.
 *
 * "Não repita o atalho do LifeOS original, que cravou o token no
 * código-fonte" (docs-site/features/copiloto-mcp.html): aqui são N alunos de
 * N instituições, então cada um tem o PRÓPRIO token, com o MENOR escopo
 * possível — `mcp:read` sempre; `mcp:write` só se pedido explicitamente,
 * porque a única tool de escrita (`criar_anotacao`) é a de menor consequência
 * do sistema, mas ainda assim escrita.
 *
 * "Trocável": nunca acumula tokens de copiloto — revoga o anterior antes de
 * emitir o novo, então só existe um por vez.
 */
final class IssueMcpTokenAction extends AbstractAction
{
    private const TOKEN_NAME = 'mcp';

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'allow_write' => ['nullable', 'boolean'],
        ];
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    protected function handle(array $data): array
    {
        $user = User::query()->findOrFail($data['user_id']);

        $user->tokens()->where('name', self::TOKEN_NAME)->delete();

        $abilities = ($data['allow_write'] ?? false) ? ['mcp:read', 'mcp:write'] : ['mcp:read'];

        return [
            'token' => $user->createToken(self::TOKEN_NAME, $abilities)->plainTextToken,
            'abilities' => $abilities,
        ];
    }
}
