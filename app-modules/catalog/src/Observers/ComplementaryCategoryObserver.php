<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria de ComplementaryCategory. Dado mestre sem coluna sensível — o que
 * importa é QUEM mexeu no teto, porque mexer nele muda quantas horas contam
 * pra toda uma coorte.
 */
final class ComplementaryCategoryObserver extends AuditObserver {}
