<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurriculumSubjects\Pages;

use App\Filament\Resources\CurriculumSubjects\CurriculumSubjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCurriculumSubject extends CreateRecord
{
    protected static string $resource = CurriculumSubjectResource::class;
}
