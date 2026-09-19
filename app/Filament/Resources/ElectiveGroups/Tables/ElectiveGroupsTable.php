<?php

declare(strict_types=1);

namespace App\Filament\Resources\ElectiveGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ElectiveGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('elg_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('curriculum.cur_name')
                    ->label('Matriz')
                    ->searchable(),
                TextColumn::make('elg_code')
                    ->searchable(),
                TextColumn::make('elg_name')
                    ->searchable(),
                TextColumn::make('elg_required_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('elg_weekly_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('elg_first_term')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('elg_last_term')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('elg_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('elg_updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
