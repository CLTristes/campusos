<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

enum CourseShift: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Evening = 'evening';
    case FullTime = 'full_time';

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Matutino',
            self::Afternoon => 'Vespertino',
            self::Evening => 'Noturno',
            self::FullTime => 'Integral',
        };
    }
}
