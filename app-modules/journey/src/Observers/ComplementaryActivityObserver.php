<?php

declare(strict_types=1);

namespace CampusOs\Journey\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria da atividade complementar.
 *
 * `cac_certificate_path` fica FORA do diff: o caminho pode mudar num deploy,
 * e o que a trilha precisa registrar é a decisão sobre as horas, não onde o
 * byte do certificado está guardado.
 */
final class ComplementaryActivityObserver extends AuditObserver
{
    /** @var list<string> */
    protected array $hidden = ['cac_certificate_path'];
}
