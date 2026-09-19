<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * RBAC de borda: `role:coordinator,institution_admin`. Roda DEPOIS de
 * `tenant.user` (precisa de `$request->user()` já resolvido).
 *
 * Genérico de propósito — a pergunta "quais papéis enxergam isto" é decisão
 * de cada rota, não deste middleware. Primeiro uso: o painel da coordenação
 * (B8), que também tem a sua própria pergunta de negócio em
 * `UserRole::seesInsights()` — as duas dizem a mesma coisa hoje; se um dia
 * divergirem, a rota decide qual delas usar.
 */
final class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null || ! in_array($user->usr_role->value, $roles, true)) {
            throw new AccessDeniedHttpException('Você não tem permissão para acessar este recurso.');
        }

        return $next($request);
    }
}
