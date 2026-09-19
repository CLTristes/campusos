<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notes\Schemas;

use CampusOs\Lifeos\Enums\NoteKind;
use CampusOs\Lifeos\Enums\Visibility;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoteForm
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
                Select::make('author_usr_id')
                    ->label('Autor')
                    ->relationship(name: 'author', titleAttribute: 'usr_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('subject_sbj_id')
                    ->label('Disciplina')
                    ->relationship(name: 'subject', titleAttribute: 'sbj_name')
                    ->searchable()
                    ->preload(),
                Select::make('offering_ofr_id')
                    ->label('Turma/oferta')
                    ->relationship(name: 'offering', titleAttribute: 'ofr_class_code')
                    ->searchable()
                    ->preload(),
                Select::make('course_crs_id')
                    ->label('Curso')
                    ->relationship(name: 'course', titleAttribute: 'crs_name')
                    ->searchable()
                    ->preload(),
                Select::make('term_trm_id')
                    ->label('Semestre')
                    ->relationship(name: 'term')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => $record->label())
                    ->searchable(['trm_year', 'trm_period'])
                    ->preload(),
                TextInput::make('nte_title')
                    ->required(),
                Textarea::make('nte_body_md')
                    ->rows(6)
                    ->columnSpanFull()
                    ->required(),
                Select::make('nte_kind')
                    ->options(NoteKind::class)
                    ->required(),
                Select::make('nte_visibility')
                    ->options(Visibility::class)
                    ->required(),
                DateTimePicker::make('nte_published_at'),
                DateTimePicker::make('nte_created_at'),
                DateTimePicker::make('nte_updated_at'),
                DateTimePicker::make('nte_deleted_at'),
            ]);
    }
}
