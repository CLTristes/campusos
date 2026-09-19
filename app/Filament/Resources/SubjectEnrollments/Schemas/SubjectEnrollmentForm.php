<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments\Schemas;

use CampusOs\Catalog\Models\Offering;
use CampusOs\Catalog\Models\Term;
use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Enums\EnrollmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubjectEnrollmentForm
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
                Select::make('subject_sbj_id')
                    ->relationship(name: 'subject', titleAttribute: 'sbj_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('term_trm_id')
                    ->relationship(name: 'term')
                    ->getOptionLabelFromRecordUsing(fn (Term $record): string => $record->label())
                    ->searchable(['trm_year', 'trm_period'])
                    ->preload()
                    ->required(),
                Select::make('offering_ofr_id')
                    ->relationship(name: 'offering', modifyQueryUsing: fn ($query) => $query->with('subject'))
                    ->getOptionLabelFromRecordUsing(fn (Offering $record): string => trim("{$record->subject?->sbj_name} — {$record->ofr_class_code}"))
                    ->searchable()
                    ->preload(),
                Select::make('sen_status')
                    ->options(EnrollmentStatus::class)
                    ->required(),
                TextInput::make('sen_class_code'),
                TextInput::make('sen_class_type'),
                TextInput::make('sen_grade')
                    ->numeric(),
                TextInput::make('sen_attendance')
                    ->numeric(),
                TextInput::make('sen_hours_earned')
                    ->numeric()
                    ->required(),
                Select::make('sen_source')
                    ->options(EnrollmentSource::class)
                    ->required(),
                DateTimePicker::make('sen_created_at'),
                DateTimePicker::make('sen_updated_at'),
                DateTimePicker::make('sen_deleted_at'),
            ]);
    }
}
