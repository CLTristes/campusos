<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offerings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfferingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('term_trm_id')
                    ->required(),
                TextInput::make('subject_sbj_id')
                    ->required(),
                TextInput::make('campus_cps_id')
                    ->required(),
                TextInput::make('ofr_class_code')
                    ->required(),
                TextInput::make('ofr_professor_name'),
                TextInput::make('ofr_schedule'),
                TextInput::make('ofr_seats')
                    ->numeric(),
                DateTimePicker::make('ofr_created_at'),
                DateTimePicker::make('ofr_updated_at'),
                DateTimePicker::make('ofr_deleted_at'),
            ]);
    }
}
