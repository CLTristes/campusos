<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ResolveTenantFromHeader;
use App\Http\Middleware\ResolveTenantFromUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliases de middleware de BORDA. `resolve.tenant` é o placeholder
        // didático do template — troque a classe pelo seu middleware real de
        // autenticação (token/hash) mantendo o alias, e nenhuma rota muda.
        $middleware->alias([
            // Placeholder didático do template (header X-Tenant-Id) — segue
            // valendo nas rotas públicas de leitura do catálogo.
            'resolve.tenant' => ResolveTenantFromHeader::class,
            // A borda real: tenant vindo do usuário autenticado. Vale para a API
            // (token Sanctum) e para o painel /data-console (sessão web).
            'tenant.user' => ResolveTenantFromUser::class,
            // RBAC de borda: role:coordinator,institution_admin. Roda depois
            // de tenant.user (precisa do usuário autenticado já resolvido).
            'role' => EnsureUserHasRole::class,
            // Escopo de token Sanctum: abilities:mcp:read. Usado só pela rota
            // do copiloto MCP (B8) — o token de login normal não tem
            // abilities restritas, então isto nunca afeta o resto da API.
            'abilities' => CheckAbilities::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
