<?php

declare(strict_types=1);

use CampusOs\Catalog\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas do módulo catalog.
 *
 * O internachi/modular dá `require` em todo arquivo de app-modules/x/routes/ no
 * boot, FORA de qualquer grupo — então o grupo de middleware é declarado aqui,
 * explicitamente. Sem o grupo `api` não há SubstituteBindings e o route model
 * binding do {course} não resolve.
 *
 * `resolve.tenant` é o placeholder do template (header X-Tenant-Id). Quando o
 * login Sanctum entrar, troca-se o alias na borda e nenhuma rota muda.
 */
Route::middleware(['api', 'resolve.tenant'])
    ->prefix('api/v1')
    ->group(function (): void {
        Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{course}/curriculum', [CourseController::class, 'curriculum'])->name('courses.curriculum');
    });
