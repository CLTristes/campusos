<?php

declare(strict_types=1);

namespace Modules\Orders\Observers;

use Modules\Core\Observers\AuditObserver;

/**
 * [EXEMPLO — REMOVÍVEL] Auditoria do pedido. `ord_internal_notes` fica fora do
 * diff de propósito: é o exemplo de coluna sensível que não pode vazar para o
 * audit_log (o análogo, num sistema real, de senhas cifradas, XMLs assinados,
 * payloads de terceiros).
 */
final class OrderObserver extends AuditObserver
{
    protected array $hidden = ['ord_internal_notes'];
}
