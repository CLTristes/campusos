<?php

declare(strict_types=1);

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('std_id'),
                TextColumn::make('entity_ent_id'),
                TextColumn::make('user_usr_id'),
                TextColumn::make('std_name')
                    ->searchable(),
                TextColumn::make('std_document')
                    ->searchable(),
                TextColumn::make('std_born_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('std_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('std_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('std_deleted_at')
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
