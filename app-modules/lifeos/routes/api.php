<?php

declare(strict_types=1);

use CampusOs\Lifeos\Http\Controllers\AgendaController;
use CampusOs\Lifeos\Http\Controllers\NoteController;
use CampusOs\Lifeos\Http\Controllers\TaskController;
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
        Route::post('notes/{note}/vote', [NoteController::class, 'vote'])->name('notes.vote');

        // B6 — tarefas da turma.
        Route::get('me/agenda', [AgendaController::class, 'me'])->name('me.agenda');
        Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::post('tasks/{task}/adopt', [TaskController::class, 'adopt'])->name('tasks.adopt');
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    });
