<?php

declare(strict_types=1);

use CampusOs\Journey\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas do módulo journey. O modular dá require fora de grupo — daí o `api`
 * explícito (mesmo comentário de catalog/routes/api.php).
 */
Route::middleware(['api', 'auth:sanctum', 'tenant.user'])
    ->prefix('api/v1')
    ->group(function (): void {
        Route::get('me/progress', [ProgressController::class, 'me'])->name('me.progress');
    });
