<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments\Pages;

use App\Filament\Resources\SubjectEnrollments\SubjectEnrollmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubjectEnrollments extends ListRecords
{
    protected static string $resource = SubjectEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
