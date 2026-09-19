<?php

declare(strict_types=1);

namespace App\Filament\Resources\CurriculumSubjects;

use App\Filament\Resources\CurriculumSubjects\Pages\CreateCurriculumSubject;
use App\Filament\Resources\CurriculumSubjects\Pages\EditCurriculumSubject;
use App\Filament\Resources\CurriculumSubjects\Pages\ListCurriculumSubjects;
use App\Filament\Resources\CurriculumSubjects\Schemas\CurriculumSubjectForm;
use App\Filament\Resources\CurriculumSubjects\Tables\CurriculumSubjectsTable;
use BackedEnum;
use CampusOs\Catalog\Models\CurriculumSubject;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CurriculumSubjectResource extends Resource
{
    protected static ?string $model = CurriculumSubject::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo acadêmico';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Disciplina na matriz';

    protected static ?string $pluralModelLabel = 'Disciplinas na matriz';

    public static function form(Schema $schema): Schema
    {
        return CurriculumSubjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurriculumSubjectsTable::configure($table);
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
            'index' => ListCurriculumSubjects::route('/'),
            'create' => CreateCurriculumSubject::route('/create'),
            'edit' => EditCurriculumSubject::route('/{record}/edit'),
        ];
    }
}
