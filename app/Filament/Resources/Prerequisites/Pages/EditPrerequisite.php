<?php

declare(strict_types=1);

namespace App\Filament\Resources\Prerequisites\Pages;

use App\Filament\Resources\Prerequisites\PrerequisiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrerequisite extends EditRecord
{
    protected static string $resource = PrerequisiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
