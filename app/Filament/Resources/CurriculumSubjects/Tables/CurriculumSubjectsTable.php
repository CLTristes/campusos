<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurriculumSubjects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurriculumSubjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cbs_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('curriculum.cur_name')
                    ->label('Matriz')
                    ->searchable(),
                TextColumn::make('subject.sbj_name')
                    ->label('Disciplina')
                    ->searchable(),
                TextColumn::make('electiveGroup.elg_name')
                    ->label('Conjunto de optativas')
                    ->searchable(),
                TextColumn::make('cbs_term')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cbs_nature')
                    ->badge()
                    ->searchable(),
                TextColumn::make('cbs_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cbs_updated_at')
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
