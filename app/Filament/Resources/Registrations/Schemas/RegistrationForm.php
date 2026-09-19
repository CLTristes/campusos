<?php

declare(strict_types=1);

namespace App\Filament\Resources\Registrations\Schemas;

use CampusOs\Journey\Enums\RegistrationStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('student_std_id')
                    ->required(),
                TextInput::make('course_crs_id')
                    ->required(),
                TextInput::make('curriculum_cur_id')
                    ->required(),
                TextInput::make('entry_term_trm_id')
                    ->required(),
                TextInput::make('reg_number')
                    ->required(),
                Select::make('reg_status')
                    ->options(RegistrationStatus::class)
                    ->required(),
                DatePicker::make('reg_graduated_at'),
                DateTimePicker::make('reg_created_at'),
                DateTimePicker::make('reg_updated_at'),
                DateTimePicker::make('reg_deleted_at'),
            ]);
    }
}
