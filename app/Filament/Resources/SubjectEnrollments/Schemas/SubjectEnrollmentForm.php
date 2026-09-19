<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments\Schemas;

use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Enums\EnrollmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectEnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('registration_reg_id')
                    ->required(),
                TextInput::make('subject_sbj_id')
                    ->required(),
                TextInput::make('term_trm_id')
                    ->required(),
                TextInput::make('offering_ofr_id'),
                Select::make('sen_status')
                    ->options(EnrollmentStatus::class)
                    ->required(),
                TextInput::make('sen_class_code'),
                TextInput::make('sen_class_type'),
                TextInput::make('sen_grade')
                    ->numeric(),
                TextInput::make('sen_attendance')
                    ->numeric(),
                TextInput::make('sen_hours_earned')
                    ->numeric()
                    ->required(),
                Select::make('sen_source')
                    ->options(EnrollmentSource::class)
                    ->required(),
                DateTimePicker::make('sen_created_at'),
                DateTimePicker::make('sen_updated_at'),
                DateTimePicker::make('sen_deleted_at'),
            ]);
    }
}
