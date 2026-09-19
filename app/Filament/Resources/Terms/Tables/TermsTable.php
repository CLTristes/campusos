<?php

declare(strict_types=1);

namespace App\Filament\Resources\Terms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trm_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('trm_year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trm_period')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('trm_starts_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('trm_ends_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('trm_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('trm_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('trm_updated_at')
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
