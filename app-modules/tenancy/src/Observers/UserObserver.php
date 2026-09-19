<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria do usuário. O hash da senha e o remember_token NUNCA entram no diff
 * de auditoria — regra de ouro nº 8. Repare que `$hidden` aqui é do OBSERVER
 * (trilha de auditoria) e é independente do `$hidden` do Model (serialização):
 * os dois precisam existir, e os dois precisam de teste.
 */
final class UserObserver extends AuditObserver
{
    /** @var list<string> */
    protected array $hidden = ['usr_password', 'remember_token', 'usr_verification_code'];
}
