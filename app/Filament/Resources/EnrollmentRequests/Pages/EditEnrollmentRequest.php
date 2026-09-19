<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnrollmentRequests\Pages;

use App\Filament\Resources\EnrollmentRequests\EnrollmentRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnrollmentRequest extends EditRecord
{
    protected static string $resource = EnrollmentRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
