<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditLogs\Tables;

use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('aud_id'),
                TextColumn::make('entity_ent_id')
                    ->label('Instituição')
                    ->formatStateUsing(fn (?string $state): string => $state !== null
                        ? (Entity::withoutGlobalScopes()->find($state)?->ent_name ?? $state)
                        : '—')
                    ->searchable(),
                TextColumn::make('aud_user_id')
                    ->label('Usuário')
                    ->formatStateUsing(fn (?string $state): string => $state !== null
                        ? (User::withoutGlobalScopes()->find($state)?->usr_name ?? $state)
                        : '—')
                    ->searchable(),
                TextColumn::make('aud_table')
                    ->searchable(),
                TextColumn::make('aud_record_id')
                    ->searchable(),
                TextColumn::make('aud_action')
                    ->badge()
                    ->searchable(),
                TextColumn::make('aud_created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('aud_created_at', 'desc')
            ->filters([
                //
            ]);
    }
}
