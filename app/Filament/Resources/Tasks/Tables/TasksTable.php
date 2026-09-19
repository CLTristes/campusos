<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tsk_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('owner.usr_name')
                    ->label('Dono')
                    ->searchable(),
                TextColumn::make('tsk_title')
                    ->searchable(),
                TextColumn::make('tsk_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('tsk_kind')
                    ->badge()
                    ->searchable(),
                TextColumn::make('tsk_visibility')
                    ->badge()
                    ->searchable(),
                TextColumn::make('subject.sbj_name')
                    ->label('Disciplina')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('offering.ofr_class_code')
                    ->label('Turma')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('origin.tsk_title')
                    ->label('Origem')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('tsk_due_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('tsk_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('tsk_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('tsk_deleted_at')
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
