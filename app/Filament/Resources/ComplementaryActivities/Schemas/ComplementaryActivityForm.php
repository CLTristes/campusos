<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryActivities\Schemas;

use CampusOs\Journey\Enums\ActivityStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ComplementaryActivityForm
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
                Select::make('registration_reg_id')
                    ->relationship(name: 'registration', titleAttribute: 'reg_number')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('complementary_category_ccg_id')
                    ->label('Categoria')
                    ->relationship(name: 'category', titleAttribute: 'ccg_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('cac_title')
                    ->required(),
                TextInput::make('cac_hours_claimed')
                    ->numeric()
                    ->required(),
                TextInput::make('cac_hours_granted')
                    ->numeric(),
                TextInput::make('cac_certificate_path'),
                DatePicker::make('cac_issued_at'),
                Select::make('cac_status')
                    ->options(ActivityStatus::class)
                    ->required(),
                Select::make('reviewer_usr_id')
                    ->label('Revisor')
                    ->relationship(name: 'reviewer', titleAttribute: 'usr_name')
                    ->searchable()
                    ->preload(),
                Textarea::make('cac_review_notes')
                    ->columnSpanFull(),
                DateTimePicker::make('cac_created_at'),
                DateTimePicker::make('cac_updated_at'),
                DateTimePicker::make('cac_deleted_at'),
            ]);
    }
}
