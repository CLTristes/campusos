<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sbj_id'),
                TextColumn::make('entity_ent_id'),
                TextColumn::make('sbj_code')
                    ->searchable(),
                TextColumn::make('sbj_name')
                    ->searchable(),
                TextColumn::make('sbj_model')
                    ->badge()
                    ->searchable(),
                TextColumn::make('sbj_weekly_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sbj_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sbj_extension_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sbj_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sbj_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sbj_deleted_at')
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
