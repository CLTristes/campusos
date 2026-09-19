<?php

declare(strict_types=1);

namespace App\Filament\Resources\Entities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EntityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ent_name')
                    ->required(),
                TextInput::make('ent_email')
                    ->email(),
                DateTimePicker::make('ent_created_at'),
                DateTimePicker::make('ent_updated_at'),
                DateTimePicker::make('ent_deleted_at'),
            ]);
    }
}
