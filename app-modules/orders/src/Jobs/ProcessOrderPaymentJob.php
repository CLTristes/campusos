<?php

declare(strict_types=1);

namespace Modules\Orders\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\Events\OrderPaid;
use Modules\Core\Tenancy\TenantContext;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

/**
 * [EXEMPLO — REMOVÍVEL] O Job canônico do template. Cada linha aqui é uma regra
 * de ouro em ação:
 *
 *  - carrega IDs primitivos (nunca models serializados) no construtor;
 *  - PRIMEIRA coisa no handle(): restaurar o tenant com TenantContext::runAs()
 *    — o worker não tem sessão HTTP (regra nº 4);
 *  - é idempotente: se a fila reentregar o job, o guard de status não reprocessa;
 *  - fala com o outro módulo SÓ pelo contrato (PaymentGateway, injetado pelo
 *    container no handle) — nunca importa classe do módulo payments (regra nº 3);
 *  - recusa DE NEGÓCIO vira estado final (payment_failed); erro de TRANSPORTE
 *    (PaymentGatewayUnavailableException) NÃO é capturado — sobe e a fila
 *    reprocessa com backoff até esgotar tries (regra nº 7);
 *  - sucesso publica um evento de domínio sem conhecer os ouvintes (regra nº 3).
 */
final class ProcessOrderPaymentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public readonly string $orderId,
        public readonly string $entityId,
    ) {}

    /** @return list<int> backoff exponencial entre tentativas, em segundos */
    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }

    public function handle(PaymentGateway $gateway): void
    {
        TenantContext::runAs($this->entityId, function () use ($gateway): void {
            $order = Order::query()->findOrFail($this->orderId);

            // Reentrega da fila após sucesso parcial? Não reprocessa (idempotente).
            if ($order->ord_status !== OrderStatus::ProcessingPayment) {
                return;
            }

            // Transporte falhou? A exceção SOBE daqui — nunca a capture (regra nº 7).
            $result = $gateway->charge($order->ord_id, (float) $order->ord_amount);

            if (! $result->approved) {
                $order->update([
                    'ord_status' => OrderStatus::PaymentFailed,
                    'ord_failure_reason' => $result->declineReason,
                ]);

                return;
            }

            $order->update([
                'ord_status' => OrderStatus::Paid,
                'ord_paid_at' => now(),
            ]);

            // Avisa o mundo sem conhecer ninguém: notifications (e quem mais
            // escutar amanhã) reage por conta própria.
            event(new OrderPaid(
                orderId: $order->ord_id,
                entityId: (string) $order->entity_ent_id,
                ref: $order->ord_ref,
                amount: (float) $order->ord_amount,
            ));
        });
    }
}
