<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurriculumSubjects\Pages;

use App\Filament\Resources\CurriculumSubjects\CurriculumSubjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurriculumSubject extends EditRecord
{
    protected static string $resource = CurriculumSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
