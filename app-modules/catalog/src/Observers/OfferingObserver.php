<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria de Offering. Dado mestre do catálogo não tem coluna sensível — o que
 * importa aqui é QUEM mexeu na matriz, porque mexer nela muda a barra de
 * progresso de toda uma coorte.
 */
final class OfferingObserver extends AuditObserver {}
