<?php

declare(strict_types=1);

use CampusOs\Tenancy\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas do módulo tenancy.
 *
 * O modular dá require nestes arquivos fora de qualquer grupo — daí o grupo
 * `api` explícito (ver o mesmo comentário em catalog/routes/api.php).
 *
 * `tenant.user` roda DEPOIS de `auth:sanctum`: a ordem importa, porque ele lê
 * o usuário já autenticado para popular o TenantContext.
 */
Route::middleware('api')->prefix('api/v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('auth.login');

    Route::middleware(['auth:sanctum', 'tenant.user'])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });
});
