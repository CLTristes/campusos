<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notes;

use App\Filament\Resources\Notes\Pages\CreateNote;
use App\Filament\Resources\Notes\Pages\EditNote;
use App\Filament\Resources\Notes\Pages\ListNotes;
use App\Filament\Resources\Notes\Schemas\NoteForm;
use App\Filament\Resources\Notes\Tables\NotesTable;
use BackedEnum;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Lifeos\Scopes\NoteVisibilityScope;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Acervo e tarefas';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Anotação';

    protected static ?string $pluralModelLabel = 'Anotações';

    public static function form(Schema $schema): Schema
    {
        return NoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotesTable::configure($table);
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
            'index' => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'edit' => EditNote::route('/{record}/edit'),
        ];
    }

    /**
     * O console é ferramenta de coordenação/gestão — precisa enxergar TODA
     * anotação da instituição, não só o que `NoteVisibilityScope` liberaria
     * pro usuário autenticado (que nem é aluno). Sem isso, a listagem
     * mostraria um recorte inconsistente e dependente de quem logou no
     * painel. Mesmo scope que `Note::scopeVisibleTo()` já remove.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope(NoteVisibilityScope::class);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withoutGlobalScope(NoteVisibilityScope::class);
    }
}
