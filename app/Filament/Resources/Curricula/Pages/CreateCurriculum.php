<?php

declare(strict_types=1);

namespace App\Filament\Resources\Curricula\Pages;

use App\Filament\Resources\Curricula\CurriculumResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCurriculum extends CreateRecord
{
    protected static string $resource = CurriculumResource::class;
}
