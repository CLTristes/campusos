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
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('curriculum_cur_id')
                    ->required(),
                TextInput::make('subject_sbj_id')
                    ->required(),
                TextInput::make('elective_group_elg_id'),
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
