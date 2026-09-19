<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offerings\Schemas;

use CampusOs\Catalog\Models\Term;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OfferingForm
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
                Select::make('term_trm_id')
                    ->relationship(name: 'term')
                    ->getOptionLabelFromRecordUsing(fn (Term $record): string => $record->label())
                    ->searchable(['trm_year', 'trm_period'])
                    ->preload()
                    ->required(),
                Select::make('subject_sbj_id')
                    ->relationship(name: 'subject', titleAttribute: 'sbj_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('campus_cps_id')
                    ->relationship(name: 'campus', titleAttribute: 'cps_name')
                    ->searchable()
                    ->preload()
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
