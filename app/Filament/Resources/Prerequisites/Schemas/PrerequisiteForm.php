<?php

declare(strict_types=1);

namespace App\Filament\Resources\Prerequisites\Schemas;

use CampusOs\Catalog\Enums\PrerequisiteType;
use CampusOs\Catalog\Models\CurriculumSubject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PrerequisiteForm
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
                    ->label('Disciplina (da matriz)')
                    ->relationship(
                        name: 'curriculumSubject',
                        modifyQueryUsing: fn ($query) => $query->with('subject'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (CurriculumSubject $record): string => $record->subject?->sbj_name ?? $record->cbs_id)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('required_cbs_id')
                    ->label('Pré-requisito (disciplina exigida)')
                    ->relationship(
                        name: 'required',
                        modifyQueryUsing: fn ($query) => $query->with('subject'),
                    )
                    ->getOptionLabelFromRecordUsing(fn (CurriculumSubject $record): string => $record->subject?->sbj_name ?? $record->cbs_id)
                    ->searchable()
                    ->preload(),
                Select::make('prq_type')
                    ->options(PrerequisiteType::class)
                    ->required(),
                TextInput::make('prq_min_term')
                    ->numeric(),
                TextInput::make('prq_min_hours')
                    ->numeric(),
            ]);
    }
}
