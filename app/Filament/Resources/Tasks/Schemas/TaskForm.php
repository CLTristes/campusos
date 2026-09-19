<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Schemas;

use CampusOs\Lifeos\Enums\TaskKind;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
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
                Select::make('owner_usr_id')
                    ->label('Dono')
                    ->relationship(name: 'owner', titleAttribute: 'usr_name')
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
                TextInput::make('project_prj_id')
                    ->label('Projeto (sem tabela ainda — ◎ esticada)'),
                Select::make('origin_tsk_id')
                    ->label('Tarefa de origem (se for adotada)')
                    ->relationship(name: 'origin', titleAttribute: 'tsk_title')
                    ->searchable()
                    ->preload(),
                TextInput::make('tsk_title')
                    ->required(),
                Textarea::make('tsk_description_md')
                    ->rows(4)
                    ->columnSpanFull(),
                Select::make('tsk_status')
                    ->options(TaskStatus::class)
                    ->required(),
                Select::make('tsk_kind')
                    ->options(TaskKind::class)
                    ->required(),
                DateTimePicker::make('tsk_due_at'),
                Select::make('tsk_visibility')
                    ->options(Visibility::class)
                    ->required(),
                DateTimePicker::make('tsk_created_at'),
                DateTimePicker::make('tsk_updated_at'),
                DateTimePicker::make('tsk_deleted_at'),
            ]);
    }
}
