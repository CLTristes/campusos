<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditLogs;

use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Resources\AuditLogs\Tables\AuditLogsTable;
use BackedEnum;
use CampusOs\Core\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * Sem Create nem Edit de propósito: `AuditLog` é imutável por desenho (regra
 * de ouro nº 8) — só o `AuditObserver` escreve aqui. O console mostra, nunca
 * altera. Sem `Entityable` também de propósito no Model (auditoria não é
 * escopada por tenant), então este Resource não reintroduz um escopo que o
 * Model rejeitou conscientemente.
 */
class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Auditoria';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Registro de auditoria';

    protected static ?string $pluralModelLabel = 'Auditoria';

    public static function table(Table $table): Table
    {
        return AuditLogsTable::configure($table);
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
            'index' => ListAuditLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
