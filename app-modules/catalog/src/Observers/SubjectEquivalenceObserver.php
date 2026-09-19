<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Observers;

use CampusOs\Core\Observers\AuditObserver;

/** Auditoria da equivalência — mexer nela reescreve o histórico de quem mudou de matriz. */
final class SubjectEquivalenceObserver extends AuditObserver {}
