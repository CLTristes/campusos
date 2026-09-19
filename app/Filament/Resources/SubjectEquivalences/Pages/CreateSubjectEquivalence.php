<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences\Pages;

use App\Filament\Resources\SubjectEquivalences\SubjectEquivalenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubjectEquivalence extends CreateRecord
{
    protected static string $resource = SubjectEquivalenceResource::class;
}
