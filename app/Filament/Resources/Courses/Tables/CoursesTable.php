<?php

declare(strict_types=1);

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('crs_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('campus.cps_name')
                    ->label('Câmpus')
                    ->searchable(),
                TextColumn::make('crs_code')
                    ->searchable(),
                TextColumn::make('crs_name')
                    ->searchable(),
                TextColumn::make('crs_degree')
                    ->badge()
                    ->searchable(),
                TextColumn::make('crs_shift')
                    ->badge()
                    ->searchable(),
                TextColumn::make('crs_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('crs_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('crs_deleted_at')
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
