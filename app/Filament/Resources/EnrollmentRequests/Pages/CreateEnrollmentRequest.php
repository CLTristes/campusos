<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnrollmentRequests\Pages;

use App\Filament\Resources\EnrollmentRequests\EnrollmentRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollmentRequest extends CreateRecord
{
    protected static string $resource = EnrollmentRequestResource::class;
}
