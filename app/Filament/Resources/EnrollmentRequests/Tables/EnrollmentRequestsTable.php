<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnrollmentRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EnrollmentRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('erq_id'),
                TextColumn::make('entity.ent_name')
                    ->label('Instituição')
                    ->searchable(),
                TextColumn::make('user.usr_name')
                    ->label('Aluno')
                    ->searchable(),
                TextColumn::make('registration.reg_number')
                    ->label('Vínculo')
                    ->searchable(),
                TextColumn::make('erq_kind')
                    ->badge(),
                TextColumn::make('erq_status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('erq_confidence')
                    ->numeric(),
                TextColumn::make('erq_provider')
                    ->searchable(),
                TextColumn::make('erq_failure_reason')
                    ->limit(50),
                TextColumn::make('erq_parsed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('erq_confirmed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('erq_created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('erq_updated_at')
                    ->dateTime()
                    ->sortable(),
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
