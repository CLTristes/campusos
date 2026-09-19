<?php

declare(strict_types=1);

namespace Modules\Orders\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\OrderReadModel;
use Modules\Orders\ReadModels\EloquentOrderReadModel;

/**
 * [EXEMPLO — REMOVÍVEL] O provider é onde o módulo "entrega" suas capacidades:
 * o bind liga a porta do core (interface) à implementação que mora aqui dentro.
 * Quem injeta OrderReadModel nunca descobre que a classe concreta é desta pasta.
 */
class OrdersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrderReadModel::class, EloquentOrderReadModel::class);
    }

    public function boot(): void {}
}
