<?php

declare(strict_types=1);

namespace CampusOs\Journey\Enums;

/**
 * O ciclo do documento: uploaded → processing → parsed → confirmed,
 * com `failed` como estado final de recusa de negócio.
 */
enum DocumentRequestStatus: string
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Parsed = 'parsed';
    case Confirmed = 'confirmed';
    case Failed = 'failed';

    /** O Job já terminou com este documento? Guard de idempotência da fila. */
    public function isSettled(): bool
    {
        return in_array($this, [self::Parsed, self::Confirmed, self::Failed], true);
    }

    /** O front deve continuar consultando? */
    public function isPending(): bool
    {
        return in_array($this, [self::Uploaded, self::Processing], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Recebido',
            self::Processing => 'Lendo o documento',
            self::Parsed => 'Aguardando sua conferência',
            self::Confirmed => 'Confirmado',
            self::Failed => 'Não foi possível ler',
        };
    }
}
