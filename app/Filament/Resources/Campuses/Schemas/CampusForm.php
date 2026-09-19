<?php

declare(strict_types=1);

namespace App\Filament\Resources\Campuses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CampusForm
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
                TextInput::make('cps_name')
                    ->required(),
                TextInput::make('cps_code')
                    ->required(),
                TextInput::make('cps_city'),
                DateTimePicker::make('cps_created_at'),
                DateTimePicker::make('cps_updated_at'),
                DateTimePicker::make('cps_deleted_at'),
            ]);
    }
}
