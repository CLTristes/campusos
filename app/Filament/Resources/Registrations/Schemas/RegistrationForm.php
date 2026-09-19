<?php

declare(strict_types=1);

namespace App\Filament\Resources\Registrations\Schemas;

use CampusOs\Catalog\Models\Term;
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
                Select::make('entity_ent_id')
                    ->relationship(name: 'entity', titleAttribute: 'ent_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('student_std_id')
                    ->relationship(name: 'student', titleAttribute: 'std_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('course_crs_id')
                    ->relationship(name: 'course', titleAttribute: 'crs_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('curriculum_cur_id')
                    ->relationship(name: 'curriculum', titleAttribute: 'cur_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('entry_term_trm_id')
                    ->relationship(name: 'entryTerm')
                    ->getOptionLabelFromRecordUsing(fn (Term $record): string => $record->label())
                    ->searchable(['trm_year', 'trm_period'])
                    ->preload()
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
