<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryCategories\Pages;

use App\Filament\Resources\ComplementaryCategories\ComplementaryCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComplementaryCategory extends CreateRecord
{
    protected static string $resource = ComplementaryCategoryResource::class;
}
