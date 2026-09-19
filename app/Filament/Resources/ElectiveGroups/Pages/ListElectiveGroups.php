<?php

declare(strict_types=1);

namespace App\Filament\Resources\ElectiveGroups\Pages;

use App\Filament\Resources\ElectiveGroups\ElectiveGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListElectiveGroups extends ListRecords
{
    protected static string $resource = ElectiveGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
