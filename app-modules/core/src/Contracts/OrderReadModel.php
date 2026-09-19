<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DTOs\OrderSummaryDTO;

/**
 * [EXEMPLO — REMOVÍVEL] Read model do fluxo de exemplo.
 *
 * Demonstra o mecanismo nº 3 de comunicação entre módulos (docs/arquitetura/
 * COMUNICACAO.md): leitura através da fronteira. Quando outro módulo (ou o host —
 * um dashboard, um painel) precisa LER dados de pedidos, ele não recebe o model
 * `Order` cru (isso vazaria a fronteira) — recebe um DTO imutável via esta porta.
 * A implementação (EloquentOrderReadModel) vive no módulo `orders`, dono dos dados.
 */
interface OrderReadModel
{
    public function summaryById(string $orderId): ?OrderSummaryDTO;
}
