<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ComplementaryCategoryForm
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
                Select::make('curriculum_cur_id')
                    ->relationship(name: 'curriculum', titleAttribute: 'cur_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('ccg_name')
                    ->required(),
                TextInput::make('ccg_max_hours')
                    ->numeric()
                    ->required(),
                Textarea::make('ccg_conversion_note')
                    ->columnSpanFull(),
            ]);
    }
}
