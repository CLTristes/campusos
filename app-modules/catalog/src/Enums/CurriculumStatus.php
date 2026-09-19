<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

/** "STATUS: ativa" no bloco de fechamento do documento da matriz. */
enum CurriculumStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
