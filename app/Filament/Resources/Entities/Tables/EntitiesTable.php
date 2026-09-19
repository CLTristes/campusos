<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EntitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ent_id'),
                TextColumn::make('ent_name')
                    ->searchable(),
                TextColumn::make('ent_email')
                    ->searchable(),
                TextColumn::make('ent_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ent_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ent_deleted_at')
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
