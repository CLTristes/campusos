<?php

declare(strict_types=1);

namespace CampusOs\Journey\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria de Registration. Num produto acadêmico a trilha deixa de ser higiene e
 * vira requisito: "quem alterou a nota desta matrícula" é exatamente a
 * pergunta que uma coordenação faz quando algo dá errado.
 */
final class RegistrationObserver extends AuditObserver {}
