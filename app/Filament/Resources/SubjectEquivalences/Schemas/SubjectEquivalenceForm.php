<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectEquivalenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('curriculum_subject_cbs_id')
                    ->required(),
                TextInput::make('seq_code')
                    ->required(),
                TextInput::make('seq_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('seq_group')
                    ->numeric(),
                DateTimePicker::make('seq_created_at'),
                DateTimePicker::make('seq_updated_at'),
            ]);
    }
}
