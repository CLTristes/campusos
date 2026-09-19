<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEquivalences;

use App\Filament\Resources\SubjectEquivalences\Pages\CreateSubjectEquivalence;
use App\Filament\Resources\SubjectEquivalences\Pages\EditSubjectEquivalence;
use App\Filament\Resources\SubjectEquivalences\Pages\ListSubjectEquivalences;
use App\Filament\Resources\SubjectEquivalences\Schemas\SubjectEquivalenceForm;
use App\Filament\Resources\SubjectEquivalences\Tables\SubjectEquivalencesTable;
use BackedEnum;
use CampusOs\Catalog\Models\SubjectEquivalence;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubjectEquivalenceResource extends Resource
{
    protected static ?string $model = SubjectEquivalence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo acadêmico';

    protected static ?int $navigationSort = 8;

    protected static ?string $modelLabel = 'Equivalência';

    protected static ?string $pluralModelLabel = 'Equivalências';

    public static function form(Schema $schema): Schema
    {
        return SubjectEquivalenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubjectEquivalencesTable::configure($table);
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
            'index' => ListSubjectEquivalences::route('/'),
            'create' => CreateSubjectEquivalence::route('/create'),
            'edit' => EditSubjectEquivalence::route('/{record}/edit'),
        ];
    }
}
