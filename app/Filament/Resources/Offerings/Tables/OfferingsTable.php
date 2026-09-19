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
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('term.trm_year')
                    ->label('Termo')
                    ->formatStateUsing(fn ($record) => $record->term?->label() ?? '—')
                    ->sortable(),
                TextColumn::make('subject.sbj_name')
                    ->label('Disciplina')
                    ->searchable(),
                TextColumn::make('campus.cps_name')
                    ->label('Câmpus')
                    ->searchable(),
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
