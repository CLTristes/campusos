<?php

declare(strict_types=1);

namespace CampusOs\Journey\Observers;

use CampusOs\Core\Observers\AuditObserver;

/**
 * Auditoria do documento enviado.
 *
 * `erq_file_path` e `erq_extraction` ficam FORA do diff: o caminho pode mudar
 * num deploy, e a extração é um JSON inteiro de dado pessoal que engordaria
 * cada linha da trilha sem acrescentar nada. O que a auditoria precisa
 * registrar é a mudança de ESTADO do documento, não o conteúdo dele.
 */
final class EnrollmentRequestObserver extends AuditObserver
{
    /** @var list<string> */
    protected array $hidden = ['erq_file_path', 'erq_extraction'];
}
