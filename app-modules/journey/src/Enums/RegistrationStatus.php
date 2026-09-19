<?php

declare(strict_types=1);

namespace CampusOs\Journey\Enums;

/** "Situação: Regular" no cabeçalho do histórico. */
enum RegistrationStatus: string
{
    case Active = 'active';
    case Locked = 'locked';
    case Graduated = 'graduated';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Regular',
            self::Locked => 'Trancado',
            self::Graduated => 'Formado',
            self::Dismissed => 'Desligado',
        };
    }
}
