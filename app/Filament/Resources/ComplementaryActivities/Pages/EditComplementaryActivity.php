<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryActivities\Pages;

use App\Filament\Resources\ComplementaryActivities\ComplementaryActivityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditComplementaryActivity extends EditRecord
{
    protected static string $resource = ComplementaryActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
