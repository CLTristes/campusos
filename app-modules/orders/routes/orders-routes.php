<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| [EXEMPLO — REMOVÍVEL] Rotas do módulo orders
|--------------------------------------------------------------------------
|
| Cada módulo traz as próprias rotas (o internachi/modular carrega todo
| arquivo em routes/ automaticamente). O middleware `resolve.tenant` é o
| PLACEHOLDER de autenticação do template — leia o aviso em
| app/Http/Middleware/ResolveTenantFromHeader.php antes de ir a produção.
|
*/

Route::middleware(['resolve.tenant', 'throttle:60,1'])
    ->prefix('v1')
    ->group(function (): void {
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
    });
