<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurriculumSubjects\Schemas;

use CampusOs\Catalog\Enums\SubjectNature;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CurriculumSubjectForm
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
                Select::make('subject_sbj_id')
                    ->relationship(name: 'subject', titleAttribute: 'sbj_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('elective_group_elg_id')
                    ->relationship(name: 'electiveGroup', titleAttribute: 'elg_name')
                    ->searchable()
                    ->preload(),
                TextInput::make('cbs_term')
                    ->required()
                    ->numeric(),
                Select::make('cbs_nature')
                    ->options(SubjectNature::class)
                    ->required(),
                DateTimePicker::make('cbs_created_at'),
                DateTimePicker::make('cbs_updated_at'),
            ]);
    }
}
