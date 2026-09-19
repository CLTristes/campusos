<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryCategories\Pages;

use App\Filament\Resources\ComplementaryCategories\ComplementaryCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComplementaryCategories extends ListRecords
{
    protected static string $resource = ComplementaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
