<?php

declare(strict_types=1);

namespace Modules\Payments\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Payments\Gateways\FakePaymentGateway;

/**
 * [EXEMPLO — REMOVÍVEL] O bind que fecha o circuito da fronteira: a porta
 * (PaymentGateway, no core) ganha a implementação (FakePaymentGateway, daqui).
 * Escolha fixa → bind no provider; escolha por dado em runtime → resolver/factory
 * (ver docs/arquitetura/IMPLEMENTACAO.md).
 */
class PaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, FakePaymentGateway::class);
    }

    public function boot(): void {}
}
