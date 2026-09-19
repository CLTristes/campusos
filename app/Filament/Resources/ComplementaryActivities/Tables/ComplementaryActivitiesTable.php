<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryActivities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ComplementaryActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cac_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('registration.reg_number')
                    ->label('Vínculo')
                    ->searchable(),
                TextColumn::make('category.ccg_name')
                    ->label('Categoria')
                    ->searchable(),
                TextColumn::make('cac_title')
                    ->searchable(),
                TextColumn::make('cac_hours_claimed')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cac_hours_granted')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cac_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('cac_issued_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('reviewer.usr_name')
                    ->label('Revisor')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('cac_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cac_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cac_deleted_at')
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
