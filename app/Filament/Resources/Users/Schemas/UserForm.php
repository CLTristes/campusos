<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use CampusOs\Tenancy\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('entity_ent_id')
                    ->relationship(name: 'entity', titleAttribute: 'ent_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('campus_cps_id')
                    ->relationship(name: 'campus', titleAttribute: 'cps_name')
                    ->searchable()
                    ->preload(),
                Select::make('course_crs_id')
                    ->label('Curso coordenado')
                    ->relationship(name: 'course', titleAttribute: 'crs_name')
                    ->searchable()
                    ->preload()
                    ->helperText('Só relevante para coordenação — o curso que este usuário coordena (B8).'),
                TextInput::make('usr_name')
                    ->required(),
                TextInput::make('usr_email')
                    ->email()
                    ->required(),
                TextInput::make('usr_password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    // Em branco significa "não mexer": sem isto, editar qualquer
                    // outro campo do usuário sobrescreveria a senha com vazio.
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Deixe em branco para manter a senha atual.')
                    ->required(),
                Select::make('usr_role')
                    ->options(UserRole::class)
                    ->required(),
                TextInput::make('usr_registration_number'),
                DateTimePicker::make('usr_email_verified_at'),
                TextInput::make('usr_enabled_modules'),
                DateTimePicker::make('usr_created_at'),
                DateTimePicker::make('usr_updated_at'),
                DateTimePicker::make('usr_deleted_at'),
            ]);
    }
}
