<?php

declare(strict_types=1);

namespace App\Filament\Resources\ElectiveGroups\Pages;

use App\Filament\Resources\ElectiveGroups\ElectiveGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditElectiveGroup extends EditRecord
{
    protected static string $resource = ElectiveGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
