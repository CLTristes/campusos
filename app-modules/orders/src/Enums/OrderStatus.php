<?php

declare(strict_types=1);

namespace Modules\Orders\Enums;

/**
 * [EXEMPLO — REMOVÍVEL] Máquina de estados do pedido.
 *
 * Espelha o padrão de status de qualquer recurso processado assincronamente:
 * um estado "em processamento" (o que o cliente vê logo após o 202), um estado
 * final de sucesso e um estado final de erro DE NEGÓCIO. Erro de transporte não
 * tem estado: o job re-tenta até esgotar tries (aí sim vira failed_jobs).
 */
enum OrderStatus: string
{
    case ProcessingPayment = 'processing_payment';
    case Paid = 'paid';
    case PaymentFailed = 'payment_failed';
}
