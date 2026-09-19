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
                TextColumn::make('entity_ent_id'),
                TextColumn::make('curriculum_cur_id'),
                TextColumn::make('subject_sbj_id'),
                TextColumn::make('elective_group_elg_id'),
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
