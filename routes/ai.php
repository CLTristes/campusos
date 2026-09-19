<?php

declare(strict_types=1);

use App\Mcp\Servers\CampusOsServer;
use Laravel\Mcp\Facades\Mcp;

/*
 * O copiloto (B8, terceira esticada). `Mcp::web()` já registra a rota POST
 * com o middleware de protocolo MCP (headers, negociação) — aqui só
 * acrescentamos a AUTENTICAÇÃO: o mesmo `auth:sanctum` + `tenant.user` do
 * resto da API (nenhum mecanismo novo), mais `abilities:mcp:read` — o token
 * do copiloto (`POST /api/v1/auth/mcp-token`) é SEPARADO do token de login,
 * com o menor escopo possível. A ordem importa: auth resolve o usuário,
 * tenant.user lê dele o TenantContext, abilities checa o token já resolvido.
 */
Mcp::web('/mcp', CampusOsServer::class)
    ->middleware(['auth:sanctum', 'tenant.user', 'abilities:mcp:read']);
