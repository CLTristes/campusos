<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnrollmentRequests;

use App\Filament\Resources\EnrollmentRequests\Pages\CreateEnrollmentRequest;
use App\Filament\Resources\EnrollmentRequests\Pages\EditEnrollmentRequest;
use App\Filament\Resources\EnrollmentRequests\Pages\ListEnrollmentRequests;
use App\Filament\Resources\EnrollmentRequests\Schemas\EnrollmentRequestForm;
use App\Filament\Resources\EnrollmentRequests\Tables\EnrollmentRequestsTable;
use BackedEnum;
use CampusOs\Journey\Models\EnrollmentRequest;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Sem SoftDeletes — `EnrollmentRequest` não usa o trait (nasce e morre por
 * status, nunca por soft delete), então nenhuma trashed-scope aqui.
 */
class EnrollmentRequestResource extends Resource
{
    protected static ?string $model = EnrollmentRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static string|UnitEnum|null $navigationGroup = 'Jornada do aluno';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Documento importado';

    protected static ?string $pluralModelLabel = 'Documentos importados';

    public static function form(Schema $schema): Schema
    {
        return EnrollmentRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnrollmentRequestsTable::configure($table);
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
            'index' => ListEnrollmentRequests::route('/'),
            'create' => CreateEnrollmentRequest::route('/create'),
            'edit' => EditEnrollmentRequest::route('/{record}/edit'),
        ];
    }
}
