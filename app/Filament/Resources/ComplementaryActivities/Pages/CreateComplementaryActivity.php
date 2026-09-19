<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryActivities\Pages;

use App\Filament\Resources\ComplementaryActivities\ComplementaryActivityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComplementaryActivity extends CreateRecord
{
    protected static string $resource = ComplementaryActivityResource::class;
}
