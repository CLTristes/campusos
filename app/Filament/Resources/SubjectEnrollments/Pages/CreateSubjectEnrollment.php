<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments\Pages;

use App\Filament\Resources\SubjectEnrollments\SubjectEnrollmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubjectEnrollment extends CreateRecord
{
    protected static string $resource = SubjectEnrollmentResource::class;
}
