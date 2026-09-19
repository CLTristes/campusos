<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SubjectEnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sen_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('registration.reg_number')
                    ->label('Vínculo')
                    ->searchable(),
                TextColumn::make('subject.sbj_name')
                    ->label('Disciplina')
                    ->searchable(),
                TextColumn::make('term.trm_year')
                    ->label('Termo')
                    ->formatStateUsing(fn ($record) => $record->term?->label() ?? '—')
                    ->sortable(),
                TextColumn::make('offering.ofr_class_code')
                    ->label('Turma')
                    ->searchable(),
                TextColumn::make('sen_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('sen_grade')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sen_attendance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sen_hours_earned')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sen_source')
                    ->badge(),
                TextColumn::make('sen_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sen_updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sen_deleted_at')
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
