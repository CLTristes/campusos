<?php

declare(strict_types=1);

namespace Modules\Notifications\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Core\Events\OrderPaid;

/**
 * [EXEMPLO — REMOVÍVEL] Listener enfileirado (ShouldQueue): a reação ao evento
 * roda FORA do fluxo de quem emitiu — se o envio de e-mail/webhook demorar ou
 * falhar, o pagamento já está confirmado e não é afetado.
 *
 * Aqui só logamos; num sistema real este é o lugar do Mailable, do push, do
 * disparo de webhook HMAC etc. O módulo `orders` não sabe que existimos.
 */
final class SendOrderPaidNotification implements ShouldQueue
{
    public function handle(OrderPaid $event): void
    {
        Log::info('Pedido pago — notificação (exemplo) disparada.', [
            'order_id' => $event->orderId,
            'entity_id' => $event->entityId,
            'ref' => $event->ref,
            'amount' => $event->amount,
        ]);
    }
}
