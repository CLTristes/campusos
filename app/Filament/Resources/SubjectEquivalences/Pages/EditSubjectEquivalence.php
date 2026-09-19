<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences\Pages;

use App\Filament\Resources\SubjectEquivalences\SubjectEquivalenceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjectEquivalence extends EditRecord
{
    protected static string $resource = SubjectEquivalenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
