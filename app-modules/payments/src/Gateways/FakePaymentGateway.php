<?php

declare(strict_types=1);

namespace Modules\Payments\Gateways;

use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\DTOs\PaymentResult;
use Modules\Core\Exceptions\PaymentGatewayUnavailableException;

/**
 * [EXEMPLO — REMOVÍVEL] Gateway determinístico de desenvolvimento/teste — o
 * análogo do "FakePrefectureGateway" de um sistema real. Dois valores mágicos
 * permitem exercitar os três desfechos do fluxo assíncrono nos testes:
 *
 *   qualquer valor  → aprovado (transactionId gerado)
 *   999.99          → recusa DE NEGÓCIO (declined; vira estado final, sem retry)
 *   666.66          → erro de TRANSPORTE (lança; a fila reprocessa com backoff)
 *
 * Num sistema real, cada provedor externo ganha a sua classe implementando o
 * MESMO contrato (Strategy) — e trocá-los é um bind/config, nunca uma reescrita
 * de quem consome.
 */
final class FakePaymentGateway implements PaymentGateway
{
    public const DECLINE_AMOUNT = 999.99;

    public const UNAVAILABLE_AMOUNT = 666.66;

    public function charge(string $reference, float $amount): PaymentResult
    {
        if ($amount === self::UNAVAILABLE_AMOUNT) {
            throw new PaymentGatewayUnavailableException(
                'Gateway de pagamento indisponível (simulado para demonstrar retry).'
            );
        }

        if ($amount === self::DECLINE_AMOUNT) {
            return PaymentResult::declined('insufficient_funds');
        }

        return PaymentResult::approved('txn_'.bin2hex(random_bytes(8)));
    }
}
