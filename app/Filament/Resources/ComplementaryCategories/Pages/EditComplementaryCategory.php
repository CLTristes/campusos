<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryCategories\Pages;

use App\Filament\Resources\ComplementaryCategories\ComplementaryCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComplementaryCategory extends EditRecord
{
    protected static string $resource = ComplementaryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
