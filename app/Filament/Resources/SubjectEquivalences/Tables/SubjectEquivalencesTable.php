<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubjectEquivalencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seq_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('curriculumSubject.subject.sbj_name')
                    ->label('Disciplina na matriz')
                    ->searchable(),
                TextColumn::make('seq_code')
                    ->searchable(),
                TextColumn::make('seq_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('seq_group')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('seq_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('seq_updated_at')
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
