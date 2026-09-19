<?php

declare(strict_types=1);

use CampusOs\Lifeos\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas do módulo lifeos. O modular dá require fora de qualquer grupo — daí o
 * `api` explícito (mesmo comentário de catalog/routes/api.php).
 */
Route::middleware(['api', 'auth:sanctum', 'tenant.user'])
    ->prefix('api/v1')
    ->group(function (): void {
        Route::get('notes', [NoteController::class, 'index'])->name('notes.index');
        Route::post('notes', [NoteController::class, 'store'])->name('notes.store');
        Route::get('notes/{note}', [NoteController::class, 'show'])->name('notes.show');
        Route::post('notes/{note}/visibility', [NoteController::class, 'updateVisibility'])->name('notes.visibility');
    });
