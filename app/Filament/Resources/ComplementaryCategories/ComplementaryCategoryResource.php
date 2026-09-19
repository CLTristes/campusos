<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryCategories;

use App\Filament\Resources\ComplementaryCategories\Pages\CreateComplementaryCategory;
use App\Filament\Resources\ComplementaryCategories\Pages\EditComplementaryCategory;
use App\Filament\Resources\ComplementaryCategories\Pages\ListComplementaryCategories;
use App\Filament\Resources\ComplementaryCategories\Schemas\ComplementaryCategoryForm;
use App\Filament\Resources\ComplementaryCategories\Tables\ComplementaryCategoriesTable;
use BackedEnum;
use CampusOs\Catalog\Models\ComplementaryCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ComplementaryCategoryResource extends Resource
{
    protected static ?string $model = ComplementaryCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo acadêmico';

    protected static ?int $navigationSort = 9;

    protected static ?string $modelLabel = 'Categoria de horas complementares';

    protected static ?string $pluralModelLabel = 'Categorias de horas complementares';

    public static function form(Schema $schema): Schema
    {
        return ComplementaryCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplementaryCategoriesTable::configure($table);
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
            'index' => ListComplementaryCategories::route('/'),
            'create' => CreateComplementaryCategory::route('/create'),
            'edit' => EditComplementaryCategory::route('/{record}/edit'),
        ];
    }
}
