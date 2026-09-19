<?php

declare(strict_types=1);

namespace App\Filament\Resources\Registrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reg_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('student.std_name')
                    ->label('Aluno')
                    ->searchable(),
                TextColumn::make('course.crs_name')
                    ->label('Curso')
                    ->searchable(),
                TextColumn::make('curriculum.cur_name')
                    ->label('Matriz')
                    ->searchable(),
                TextColumn::make('entryTerm.trm_year')
                    ->label('Termo de ingresso')
                    ->formatStateUsing(fn ($record) => $record->entryTerm?->label() ?? '—')
                    ->sortable(),
                TextColumn::make('reg_number')
                    ->searchable(),
                TextColumn::make('reg_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('reg_graduated_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('reg_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reg_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reg_deleted_at')
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
