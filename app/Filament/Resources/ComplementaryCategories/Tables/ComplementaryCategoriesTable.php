<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComplementaryCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ccg_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('curriculum.cur_name')
                    ->label('Matriz')
                    ->searchable(),
                TextColumn::make('ccg_name')
                    ->searchable(),
                TextColumn::make('ccg_max_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ccg_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ccg_updated_at')
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
