<?php

declare(strict_types=1);

namespace App\Filament\Resources\Prerequisites\Pages;

use App\Filament\Resources\Prerequisites\PrerequisiteResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrerequisite extends CreateRecord
{
    protected static string $resource = PrerequisiteResource::class;
}
