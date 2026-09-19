<?php

declare(strict_types=1);

namespace App\Filament\Resources\Prerequisites;

use App\Filament\Resources\Prerequisites\Pages\CreatePrerequisite;
use App\Filament\Resources\Prerequisites\Pages\EditPrerequisite;
use App\Filament\Resources\Prerequisites\Pages\ListPrerequisites;
use App\Filament\Resources\Prerequisites\Schemas\PrerequisiteForm;
use App\Filament\Resources\Prerequisites\Tables\PrerequisitesTable;
use BackedEnum;
use CampusOs\Catalog\Models\Prerequisite;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrerequisiteResource extends Resource
{
    protected static ?string $model = Prerequisite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo acadêmico';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Pré-requisito';

    protected static ?string $pluralModelLabel = 'Pré-requisitos';

    public static function form(Schema $schema): Schema
    {
        return PrerequisiteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrerequisitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrerequisites::route('/'),
            'create' => CreatePrerequisite::route('/create'),
            'edit' => EditPrerequisite::route('/{record}/edit'),
        ];
    }
}
