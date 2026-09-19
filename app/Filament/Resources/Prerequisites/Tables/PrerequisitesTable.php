<?php

declare(strict_types=1);

namespace App\Filament\Resources\Prerequisites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrerequisitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prq_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('curriculumSubject.subject.sbj_name')
                    ->label('Disciplina')
                    ->searchable(),
                TextColumn::make('required.subject.sbj_name')
                    ->label('Pré-requisito')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('prq_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('prq_min_term')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prq_min_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prq_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('prq_updated_at')
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
