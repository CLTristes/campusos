<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usr_id'),
                TextColumn::make('entity_ent_id'),
                TextColumn::make('campus_cps_id'),
                TextColumn::make('usr_name')
                    ->searchable(),
                TextColumn::make('usr_email')
                    ->searchable(),
                TextColumn::make('usr_role')
                    ->badge()
                    ->searchable(),
                TextColumn::make('usr_registration_number')
                    ->searchable(),
                TextColumn::make('usr_email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('usr_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('usr_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('usr_deleted_at')
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
