<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use CampusOs\Core\Actions\Input\McpInputAdapter;
use CampusOs\Lifeos\Models\Note;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Busca por texto no título/corpo do acervo — não existe endpoint REST
 * equivalente ainda (o REST só filtra por `subject_id`). Mesmo escopo de
 * visibilidade de sempre: `NoteVisibilityScope` filtra antes da busca rodar.
 */
#[Description('Busca por palavra-chave em todo o acervo que o aluno tem acesso — título e corpo das anotações. Use quando não souber em qual disciplina procurar.')]
final class BuscarAnotacoesTool extends Tool
{
    protected string $name = 'buscar_anotacoes';

    public function handle(Request $request): Response|ResponseFactory
    {
        $data = McpInputAdapter::from($request->all());
        $query = trim((string) ($data['query'] ?? ''));

        if ($query === '') {
            throw ValidationException::withMessages([
                'query' => 'Informe o que buscar.',
            ]);
        }

        $notes = Note::query()
            ->with(['author', 'term', 'subject'])
            ->where(fn ($q) => $q->where('nte_title', 'like', "%{$query}%")->orWhere('nte_body_md', 'like', "%{$query}%"))
            // Mais votada primeiro — a curadoria contra slop (B5 esticada).
            ->orderByDesc('nte_upvotes_count')
            ->orderByRaw('COALESCE(nte_published_at, nte_created_at) DESC')
            ->limit(20)
            ->get();

        return Response::structured(['data' => $notes->map(fn (Note $note): array => [
            'id' => $note->nte_id,
            'title' => $note->nte_title,
            'kind' => $note->nte_kind->value,
            'upvotes_count' => $note->nte_upvotes_count,
            'subject_id' => $note->subject_sbj_id,
            'subject_name' => $note->relationLoaded('subject') ? $note->subject?->sbj_name : null,
            'author' => $note->relationLoaded('author') ? ['id' => $note->author->usr_id, 'name' => $note->author->usr_name] : null,
            'term' => $note->term !== null ? ['year' => $note->term->trm_year, 'period' => $note->term->trm_period] : null,
        ])->values()->all()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('A palavra ou frase a buscar no título e no corpo das anotações.')
                ->required(),
        ];
    }
}
