<?php

declare(strict_types=1);

namespace App\Filament\Resources\Terms\Schemas;

use CampusOs\Catalog\Enums\TermStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('trm_year')
                    ->required()
                    ->numeric(),
                TextInput::make('trm_period')
                    ->required()
                    ->numeric(),
                DatePicker::make('trm_starts_at'),
                DatePicker::make('trm_ends_at'),
                Select::make('trm_status')
                    ->options(TermStatus::class)
                    ->default('planned')
                    ->required(),
                DateTimePicker::make('trm_created_at'),
                DateTimePicker::make('trm_updated_at'),
            ]);
    }
}
