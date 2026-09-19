<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

/**
 * A coluna [OPT] do documento da matriz: preenchida com o código do conjunto
 * (941) ⇒ optativa; vazia ⇒ obrigatória.
 */
enum SubjectNature: string
{
    case Mandatory = 'mandatory';
    case Elective = 'elective';

    public function label(): string
    {
        return $this === self::Mandatory ? 'Obrigatória' : 'Optativa';
    }
}
