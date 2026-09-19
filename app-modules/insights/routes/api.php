<?php

declare(strict_types=1);

use CampusOs\Insights\Http\Controllers\StaffInsightsController;
use Illuminate\Support\Facades\Route;

/*
 * Rotas do módulo insights. O modular dá require fora de qualquer grupo —
 * daí o `api` explícito (mesmo comentário de catalog/routes/api.php).
 *
 * `role:coordinator,institution_admin` roda DEPOIS de `tenant.user` — precisa
 * do usuário autenticado já resolvido pra checar o papel.
 */
Route::middleware(['api', 'auth:sanctum', 'tenant.user', 'role:coordinator,institution_admin'])
    ->prefix('api/v1/staff/insights')
    ->group(function (): void {
        Route::get('bottlenecks', [StaffInsightsController::class, 'bottlenecks'])->name('staff.insights.bottlenecks');
        Route::get('cohorts', [StaffInsightsController::class, 'cohorts'])->name('staff.insights.cohorts');
        Route::get('at-risk', [StaffInsightsController::class, 'atRisk'])->name('staff.insights.at-risk');
        Route::get('demand', [StaffInsightsController::class, 'demand'])->name('staff.insights.demand');
    });
