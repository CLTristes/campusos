<?php

declare(strict_types=1);

use App\Http\Middleware\ResolveTenantFromHeader;
use App\Http\Middleware\ResolveTenantFromUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
