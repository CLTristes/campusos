<?php

declare(strict_types=1);

namespace App\Filament\Resources\Courses\Schemas;

use CampusOs\Catalog\Enums\CourseDegree;
use CampusOs\Catalog\Enums\CourseShift;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CourseForm
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
                Select::make('campus_cps_id')
                    ->relationship(name: 'campus', titleAttribute: 'cps_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('crs_code')
                    ->required(),
                TextInput::make('crs_name')
                    ->required(),
                Select::make('crs_degree')
                    ->options(CourseDegree::class)
                    ->required(),
                Select::make('crs_shift')
                    ->options(CourseShift::class),
                DateTimePicker::make('crs_created_at'),
                DateTimePicker::make('crs_updated_at'),
                DateTimePicker::make('crs_deleted_at'),
            ]);
    }
}
