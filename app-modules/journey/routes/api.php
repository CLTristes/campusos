<?php

declare(strict_types=1);

use CampusOs\Journey\Http\Controllers\AcademicDocumentController;
use CampusOs\Journey\Http\Controllers\ComplementaryActivityController;
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

        // Importação do documento acadêmico: sobe → confere → confirma.
        // O upload tem rate limit próprio: cada envio dispara uma chamada de IA.
        Route::post('me/academic-documents', [AcademicDocumentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('me.documents.store');
        Route::get('me/academic-documents/{document}', [AcademicDocumentController::class, 'show'])
            ->name('me.documents.show');
        Route::post('me/academic-documents/{document}/confirm', [AcademicDocumentController::class, 'confirm'])
            ->name('me.documents.confirm');

        // B7 — horas complementares e certificados.
        Route::get('me/complementary-activities', [ComplementaryActivityController::class, 'index'])
            ->name('me.complementary-activities.index');
        Route::post('me/complementary-activities', [ComplementaryActivityController::class, 'store'])
            ->name('me.complementary-activities.store');
    });
