<?php

declare(strict_types=1);

namespace App\Filament\Resources\Curricula\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CurriculaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cur_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('course.crs_name')
                    ->label('Curso')
                    ->searchable(),
                TextColumn::make('cur_code')
                    ->searchable(),
                TextColumn::make('cur_name')
                    ->searchable(),
                TextColumn::make('cur_version')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_effective_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('cur_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('cur_mandatory_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_elective_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_standalone_extension_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_extension_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_expected_terms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_max_terms')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_max_term_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_max_weekly_deficit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cur_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cur_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cur_deleted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
