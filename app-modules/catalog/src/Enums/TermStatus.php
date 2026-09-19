<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

enum TermStatus: string
{
    case Planned = 'planned';
    case Open = 'open';
    case Current = 'current';
    case Closed = 'closed';
}
