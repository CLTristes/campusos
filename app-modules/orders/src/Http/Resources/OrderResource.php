<?php

declare(strict_types=1);

namespace Modules\Orders\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Orders\Enums\OrderStatus;

/**
 * [EXEMPLO — REMOVÍVEL] O tradutor de saída HTTP. Por que não retornar o Model cru?
 *
 *  1. Controle do que vaza: o model tem colunas internas (ord_internal_notes) que
 *     jamais podem ir para o cliente; o Resource só expõe o que você listar.
 *  2. Formato consistente: datas ISO-8601, números com cast explícito.
 *  3. Desacopla o contrato da API da estrutura da tabela — a coluna pode mudar
 *     de nome sem quebrar os clientes.
 *
 * Resource ≠ DTO: este arquivo serve o cliente HTTP e vive DENTRO do módulo dono;
 * o OrderSummaryDTO (core) cruza a fronteira entre módulos.
 */
final class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ord_id,
            'ref' => $this->ord_ref,
            'status' => $this->ord_status->value,
            'customer_name' => $this->ord_customer_name,
            'amount' => (float) $this->ord_amount,
            'failure_reason' => $this->when(
                $this->ord_status === OrderStatus::PaymentFailed,
                $this->ord_failure_reason,
            ),
            'paid_at' => $this->ord_paid_at?->toIso8601String(),
            'created_at' => $this->ord_created_at?->toIso8601String(),
        ];
    }
}
