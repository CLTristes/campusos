<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubjectEnrollments;

use App\Filament\Resources\SubjectEnrollments\Pages\CreateSubjectEnrollment;
use App\Filament\Resources\SubjectEnrollments\Pages\EditSubjectEnrollment;
use App\Filament\Resources\SubjectEnrollments\Pages\ListSubjectEnrollments;
use App\Filament\Resources\SubjectEnrollments\Schemas\SubjectEnrollmentForm;
use App\Filament\Resources\SubjectEnrollments\Tables\SubjectEnrollmentsTable;
use BackedEnum;
use CampusOs\Journey\Models\SubjectEnrollment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SubjectEnrollmentResource extends Resource
{
    protected static ?string $model = SubjectEnrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Jornada do aluno';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Matrícula em disciplina';

    protected static ?string $pluralModelLabel = 'Matrículas em disciplinas';

    public static function form(Schema $schema): Schema
    {
        return SubjectEnrollmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubjectEnrollmentsTable::configure($table);
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
            'index' => ListSubjectEnrollments::route('/'),
            'create' => CreateSubjectEnrollment::route('/create'),
            'edit' => EditSubjectEnrollment::route('/{record}/edit'),
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
