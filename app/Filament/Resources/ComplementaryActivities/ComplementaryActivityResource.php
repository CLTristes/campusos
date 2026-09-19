<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComplementaryActivities;

use App\Filament\Resources\ComplementaryActivities\Pages\CreateComplementaryActivity;
use App\Filament\Resources\ComplementaryActivities\Pages\EditComplementaryActivity;
use App\Filament\Resources\ComplementaryActivities\Pages\ListComplementaryActivities;
use App\Filament\Resources\ComplementaryActivities\Schemas\ComplementaryActivityForm;
use App\Filament\Resources\ComplementaryActivities\Tables\ComplementaryActivitiesTable;
use BackedEnum;
use CampusOs\Journey\Models\ComplementaryActivity;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ComplementaryActivityResource extends Resource
{
    protected static ?string $model = ComplementaryActivity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Jornada do aluno';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'Atividade complementar';

    protected static ?string $pluralModelLabel = 'Atividades complementares';

    public static function form(Schema $schema): Schema
    {
        return ComplementaryActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplementaryActivitiesTable::configure($table);
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
            'index' => ListComplementaryActivities::route('/'),
            'create' => CreateComplementaryActivity::route('/create'),
            'edit' => EditComplementaryActivity::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
