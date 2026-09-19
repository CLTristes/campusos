<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryActivities\Pages;

use App\Filament\Resources\ComplementaryActivities\ComplementaryActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComplementaryActivities extends ListRecords
{
    protected static string $resource = ComplementaryActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
