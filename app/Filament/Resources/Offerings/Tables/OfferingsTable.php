<?php

declare(strict_types=1);

namespace App\Filament\Resources\Offerings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OfferingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ofr_id'),
                TextColumn::make('entity_ent_id'),
                TextColumn::make('term_trm_id'),
                TextColumn::make('subject_sbj_id'),
                TextColumn::make('campus_cps_id'),
                TextColumn::make('ofr_class_code')
                    ->searchable(),
                TextColumn::make('ofr_professor_name')
                    ->searchable(),
                TextColumn::make('ofr_seats')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ofr_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ofr_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ofr_deleted_at')
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
