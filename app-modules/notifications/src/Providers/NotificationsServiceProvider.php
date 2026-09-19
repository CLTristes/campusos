<?php

declare(strict_types=1);

namespace Modules\Notifications\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Events\OrderPaid;
use Modules\Notifications\Listeners\SendOrderPaidNotification;

/**
 * [EXEMPLO — REMOVÍVEL] Listeners registrados EXPLICITAMENTE no provider do módulo
 * ouvinte — melhor que depender de auto-discovery: o registro fica visível,
 * versionado e fácil de auditar (é assim que se responde "quem reage a OrderPaid?").
 */
class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(OrderPaid::class, SendOrderPaidNotification::class);
    }
}
