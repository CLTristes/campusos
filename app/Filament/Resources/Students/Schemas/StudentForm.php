<?php

declare(strict_types=1);

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
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
                Select::make('user_usr_id')
                    ->relationship(name: 'user', titleAttribute: 'usr_name')
                    ->searchable()
                    ->preload(),
                TextInput::make('std_name')
                    ->required(),
                TextInput::make('std_document'),
                DatePicker::make('std_born_at'),
                DateTimePicker::make('std_created_at'),
                DateTimePicker::make('std_updated_at'),
                DateTimePicker::make('std_deleted_at'),
            ]);
    }
}
