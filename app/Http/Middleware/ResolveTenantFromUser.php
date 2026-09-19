<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use CampusOs\Core\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve o tenant a partir do usuário AUTENTICADO — a borda de verdade,
 * substituindo o placeholder ResolveTenantFromHeader nas rotas com login.
 *
 * Vale para os dois canais: a API (token Sanctum) e o painel /data-console
 * (sessão web). Em ambos, `auth()->user()` já foi resolvido pelo guard; aqui só
 * se copia a instituição dele para o TenantContext, que é de onde o EntityScope
 * filtra tudo.
 *
 * Sem este middleware o painel do Filament e as rotas autenticadas enxergariam
 * zero registro — o EntityScope filtra por um tenant que ninguém definiu.
 */
final class ResolveTenantFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null, 401, 'Não autenticado.');

        // entity_ent_id é NOT NULL em users hoje; quando o admin de plataforma
        // entrar (◇ planejado), este é o ponto onde a exceção mora.
        TenantContext::set($user->entity_ent_id);

        return $next($request);
    }
}
