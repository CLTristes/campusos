<?php

declare(strict_types=1);

namespace App\Filament\Resources\ElectiveGroups\Pages;

use App\Filament\Resources\ElectiveGroups\ElectiveGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateElectiveGroup extends CreateRecord
{
    protected static string $resource = ElectiveGroupResource::class;
}
