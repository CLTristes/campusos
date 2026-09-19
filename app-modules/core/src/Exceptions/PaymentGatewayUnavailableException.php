<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use RuntimeException;

/**
 * [EXEMPLO — REMOVÍVEL] Erro de TRANSPORTE do gateway de pagamento (timeout, 5xx,
 * indisponibilidade). É lançada pela implementação e NUNCA capturada pelo Job que
 * processa o pedido — ela sobe para o mecanismo de retry da fila (tries/backoff).
 *
 * Contraste com a recusa de negócio (PaymentResult::declined), que é retorno
 * normal e vira estado final `payment_failed`. Essa distinção — transporte lança,
 * negócio retorna — é a regra de ouro nº 7 e o que torna o sistema resiliente.
 */
final class PaymentGatewayUnavailableException extends RuntimeException {}
