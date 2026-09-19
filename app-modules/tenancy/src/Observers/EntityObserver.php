<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria do tenant. Sem colunas ocultas por enquanto — quando o Entity ganhar
 * campos sensíveis (documentos, chaves), declare-os em $hidden aqui.
 */
final class EntityObserver extends AuditObserver {}
