<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Modules\Core\Tenancy\TenantContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * ⚠️ PLACEHOLDER DIDÁTICO — NÃO É AUTENTICAÇÃO. Substitua antes de produção.
 *
 * Este middleware existe para demonstrar O LUGAR onde o tenant é resolvido: na
 * BORDA (host), nunca dentro dos módulos. Ele lê um header em claro
 * (`X-Tenant-Id`) e popula o TenantContext — suficiente para desenvolver e
 * testar o fluxo multi-tenant, e nada mais.
 *
 * Num sistema real, este arquivo vira o seu `auth.token`: valida um Bearer token
 * (guardado como HASH no banco, nunca em claro — regra de ouro nº 6), resolve o
 * tenant dono do token, aplica rate limit por credencial e SÓ ENTÃO chama
 * TenantContext::set(). A assinatura do resto do sistema não muda: os módulos
 * continuam perguntando ao TenantContext, sem saber como o tenant foi resolvido.
 */
final class ResolveTenantFromHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $entityId = $request->header('X-Tenant-Id');

        abort_if(
            $entityId === null,
            401,
            'Informe o header X-Tenant-Id (placeholder de autenticação do template).'
        );

        /** @var class-string<Model> $entityModel */
        $entityModel = config('models.entity');

        abort_if(
            $entityModel::query()->whereKey($entityId)->doesntExist(),
            401,
            'Tenant não encontrado.'
        );

        TenantContext::set($entityId);

        return $next($request);
    }
}
