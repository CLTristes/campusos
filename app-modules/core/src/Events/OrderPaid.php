<?php

declare(strict_types=1);

namespace Modules\Core\Events;

/**
 * [EXEMPLO — REMOVÍVEL] Evento de domínio do fluxo de exemplo.
 *
 * Demonstra o mecanismo nº 2 de comunicação entre módulos (docs/arquitetura/
 * COMUNICACAO.md): quando o pagamento é aprovado, o módulo `orders` só AVISA —
 * `event(new OrderPaid(...))` — sem conhecer nenhum ouvinte. O módulo
 * `notifications` escuta e reage; amanhã um módulo `billing` pode escutar também,
 * sem que `orders` mude uma linha.
 *
 * Eventos de domínio carregam IDs e valores primitivos, nunca models Eloquent —
 * o payload é serializado para a fila e o model não pode cruzar a fronteira.
 */
final class OrderPaid
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $entityId,
        public readonly ?string $ref,
        public readonly float $amount,
    ) {}
}
