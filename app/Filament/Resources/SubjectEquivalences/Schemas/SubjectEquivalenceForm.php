<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences\Schemas;

use CampusOs\Catalog\Models\CurriculumSubject;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectEquivalenceForm
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
                Select::make('curriculum_subject_cbs_id')
                    ->relationship(name: 'curriculumSubject', modifyQueryUsing: fn ($query) => $query->with('subject'))
                    ->getOptionLabelFromRecordUsing(fn (CurriculumSubject $record): string => "{$record->subject?->sbj_name} (período {$record->cbs_term})")
                    ->searchable()
                    ->preload()
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
