<?php

declare(strict_types=1);

namespace App\Filament\Resources\ElectiveGroups;

use App\Filament\Resources\ElectiveGroups\Pages\CreateElectiveGroup;
use App\Filament\Resources\ElectiveGroups\Pages\EditElectiveGroup;
use App\Filament\Resources\ElectiveGroups\Pages\ListElectiveGroups;
use App\Filament\Resources\ElectiveGroups\Schemas\ElectiveGroupForm;
use App\Filament\Resources\ElectiveGroups\Tables\ElectiveGroupsTable;
use BackedEnum;
use CampusOs\Catalog\Models\ElectiveGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ElectiveGroupResource extends Resource
{
    protected static ?string $model = ElectiveGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo acadêmico';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Conjunto de optativas';

    protected static ?string $pluralModelLabel = 'Conjuntos de optativas';

    public static function form(Schema $schema): Schema
    {
        return ElectiveGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ElectiveGroupsTable::configure($table);
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
            'index' => ListElectiveGroups::route('/'),
            'create' => CreateElectiveGroup::route('/create'),
            'edit' => EditElectiveGroup::route('/{record}/edit'),
        ];
    }
}
