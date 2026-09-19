<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use CampusOs\Core\Actions\Input\McpInputAdapter;
use CampusOs\Lifeos\Models\Note;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Diferente de `acervo_da_disciplina` (o que os OUTROS deixaram numa
 * disciplina) e `buscar_anotacoes` (busca por palavra em tudo que o aluno
 * enxerga): aqui é só o que ELE PRÓPRIO escreveu, qualquer visibilidade,
 * qualquer disciplina — inclusive rascunho `private` que nunca publicou.
 * Mesmo `NoteVisibilityScope`; o filtro por autor é só um `where` a mais.
 */
#[Description('As anotações que o próprio aluno escreveu — qualquer visibilidade, qualquer disciplina, inclusive as que ainda não publicou. Filtre por subject_id pra ver só as de uma disciplina.')]
final class MinhasAnotacoesTool extends Tool
{
    protected string $name = 'minhas_anotacoes';

    public function handle(Request $request): Response|ResponseFactory
    {
        $data = McpInputAdapter::from($request->all());

        $notes = Note::query()
            ->with(['term', 'subject'])
            ->where('author_usr_id', auth()->id())
            ->when($data['subject_id'] ?? null, fn ($q, $subjectId) => $q->where('subject_sbj_id', $subjectId))
            ->orderByRaw('COALESCE(nte_published_at, nte_created_at) DESC')
            ->get();

        return Response::structured(['data' => $notes->map(fn (Note $note): array => [
            'id' => $note->nte_id,
            'title' => $note->nte_title,
            'kind' => $note->nte_kind->value,
            'visibility' => $note->nte_visibility->value,
            'subject_id' => $note->subject_sbj_id,
            'subject_name' => $note->relationLoaded('subject') ? $note->subject?->sbj_name : null,
            'term' => $note->term !== null ? ['year' => $note->term->trm_year, 'period' => $note->term->trm_period] : null,
            'published_at' => $note->nte_published_at?->toIso8601String(),
        ])->values()->all()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'subject_id' => $schema->string()->description('Filtra pelas anotações de uma disciplina (uuid). Omitido, devolve todas.'),
        ];
    }
}
