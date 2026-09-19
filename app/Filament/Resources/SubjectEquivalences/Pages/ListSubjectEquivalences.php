<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences\Pages;

use App\Filament\Resources\SubjectEquivalences\SubjectEquivalenceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjectEquivalences extends ListRecords
{
    protected static string $resource = SubjectEquivalenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
