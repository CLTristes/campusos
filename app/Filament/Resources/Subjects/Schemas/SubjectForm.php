<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subjects\Schemas;

use CampusOs\Catalog\Enums\SubjectModel;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('entity_ent_id')
                    ->required(),
                TextInput::make('sbj_code')
                    ->required(),
                TextInput::make('sbj_name')
                    ->required(),
                Select::make('sbj_model')
                    ->options(SubjectModel::class),
                TextInput::make('sbj_weekly_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sbj_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sbj_extension_hours')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sbj_workload_breakdown'),
                Textarea::make('sbj_syllabus')
                    ->columnSpanFull(),
                DateTimePicker::make('sbj_created_at'),
                DateTimePicker::make('sbj_updated_at'),
                DateTimePicker::make('sbj_deleted_at'),
            ]);
    }
}
