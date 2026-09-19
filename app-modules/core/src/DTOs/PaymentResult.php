<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

/**
 * [EXEMPLO — REMOVÍVEL] DTO de travessia de fronteira do contrato PaymentGateway.
 *
 * Um DTO é um objeto burro que carrega dados tipados — sem lógica, sem banco.
 * Ele existe para que a resposta crua do gateway (que é detalhe interno do módulo
 * `payments`) nunca atravesse a fronteira: cada implementação traduz sua resposta
 * para ESTE formato único, e o consumidor (`orders`) fala com todas do mesmo jeito.
 */
final class PaymentResult
{
    private function __construct(
        public readonly bool $approved,
        public readonly ?string $transactionId,
        public readonly ?string $declineReason,
    ) {}

    public static function approved(string $transactionId): self
    {
        return new self(approved: true, transactionId: $transactionId, declineReason: null);
    }

    public static function declined(string $reason): self
    {
        return new self(approved: false, transactionId: null, declineReason: $reason);
    }
}
