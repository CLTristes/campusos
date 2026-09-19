<?php

declare(strict_types=1);

namespace App\Filament\Resources\Curricula\Schemas;

use CampusOs\Catalog\Enums\CurriculumStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CurriculumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('course_crs_id')
                    ->required(),
                TextInput::make('cur_code')
                    ->required(),
                TextInput::make('cur_name')
                    ->required(),
                TextInput::make('cur_version')
                    ->required()
                    ->numeric()
                    ->default(1),
                DatePicker::make('cur_effective_from'),
                Select::make('cur_status')
                    ->options(CurriculumStatus::class)
                    ->default('active')
                    ->required(),
                TextInput::make('cur_mandatory_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cur_elective_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cur_standalone_extension_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cur_extension_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cur_expected_terms')
                    ->numeric(),
                TextInput::make('cur_max_terms')
                    ->numeric(),
                TextInput::make('cur_max_term_hours')
                    ->numeric(),
                TextInput::make('cur_max_weekly_deficit')
                    ->numeric(),
                DateTimePicker::make('cur_created_at'),
                DateTimePicker::make('cur_updated_at'),
                DateTimePicker::make('cur_deleted_at'),
            ]);
    }
}
