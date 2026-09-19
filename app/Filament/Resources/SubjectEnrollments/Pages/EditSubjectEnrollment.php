<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments\Pages;

use App\Filament\Resources\SubjectEnrollments\SubjectEnrollmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSubjectEnrollment extends EditRecord
{
    protected static string $resource = SubjectEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
