<?php

declare(strict_types=1);

namespace App\Filament\Resources\ElectiveGroups\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ElectiveGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('curriculum_cur_id')
                    ->required(),
                TextInput::make('elg_code')
                    ->required(),
                TextInput::make('elg_name')
                    ->required(),
                TextInput::make('elg_required_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('elg_weekly_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('elg_first_term')
                    ->numeric(),
                TextInput::make('elg_last_term')
                    ->numeric(),
                DateTimePicker::make('elg_created_at'),
                DateTimePicker::make('elg_updated_at'),
            ]);
    }
}
