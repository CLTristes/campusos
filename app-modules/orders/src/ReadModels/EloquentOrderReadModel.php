<?php

declare(strict_types=1);

namespace Modules\Orders\ReadModels;

use Modules\Core\Contracts\OrderReadModel;
use Modules\Core\DTOs\OrderSummaryDTO;
use Modules\Orders\Models\Order;

/**
 * [EXEMPLO — REMOVÍVEL] Implementação do read model — leitura através da fronteira.
 * O model `Order` entra e sai DESTE arquivo, nunca do módulo: quem consome (host,
 * dashboards, outros módulos) recebe o DTO imutável. O EntityScope continua
 * valendo aqui — o read model respeita o tenant do contexto.
 */
final class EloquentOrderReadModel implements OrderReadModel
{
    public function summaryById(string $orderId): ?OrderSummaryDTO
    {
        $order = Order::query()->find($orderId);

        if ($order === null) {
            return null;
        }

        return new OrderSummaryDTO(
            id: $order->ord_id,
            ref: $order->ord_ref,
            status: $order->ord_status->value,
            amount: (float) $order->ord_amount,
        );
    }
}
