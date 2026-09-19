<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

enum CourseDegree: string
{
    case Bachelor = 'bachelor';
    case Licentiate = 'licentiate';
    case Technologist = 'technologist';

    public function label(): string
    {
        return match ($this) {
            self::Bachelor => 'Bacharelado',
            self::Licentiate => 'Licenciatura',
            self::Technologist => 'Tecnólogo',
        };
    }
}
