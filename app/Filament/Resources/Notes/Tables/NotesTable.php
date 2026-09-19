<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class NotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nte_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('author.usr_name')
                    ->label('Autor')
                    ->searchable(),
                TextColumn::make('nte_title')
                    ->searchable(),
                TextColumn::make('nte_kind')
                    ->badge()
                    ->searchable(),
                TextColumn::make('nte_visibility')
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
                TextColumn::make('course.crs_name')
                    ->label('Curso')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('nte_published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('nte_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('nte_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('nte_deleted_at')
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
