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
 * Adaptador fino sobre a mesma listagem de `GET /api/v1/notes?subject_id=`.
 * O escopo de visibilidade é o MESMO — `NoteVisibilityScope` (global scope
 * do model `Note`) filtra antes de qualquer linha chegar aqui. Uma tool que
 * enxergasse mais que o endpoint REST seria um vazamento, e é o erro clássico
 * de adicionar um canal novo depressa (Fase 2 do desenho).
 */
#[Description('O acervo de uma disciplina: as anotações públicas (resumos, provas antigas, listas resolvidas) que o aluno tem direito de ver, deixadas por quem já cursou — em qualquer semestre.')]
final class AcervoDaDisciplinaTool extends Tool
{
    protected string $name = 'acervo_da_disciplina';

    public function handle(Request $request): Response|ResponseFactory
    {
        $data = McpInputAdapter::from($request->all());

        if (empty($data['subject_id'])) {
            throw ValidationException::withMessages([
                'subject_id' => 'Informe o id da disciplina.',
            ]);
        }

        $notes = Note::query()
            ->with(['author', 'term'])
            ->where('subject_sbj_id', $data['subject_id'])
            ->orderByRaw('COALESCE(nte_published_at, nte_created_at) DESC')
            ->get();

        return Response::structured(['data' => $notes->map(fn (Note $note): array => [
            'id' => $note->nte_id,
            'title' => $note->nte_title,
            'body_md' => $note->nte_body_md,
            'kind' => $note->nte_kind->value,
            'visibility' => $note->nte_visibility->value,
            'author' => $note->relationLoaded('author') ? ['id' => $note->author->usr_id, 'name' => $note->author->usr_name] : null,
            'term' => $note->term !== null ? ['year' => $note->term->trm_year, 'period' => $note->term->trm_period] : null,
            'published_at' => $note->nte_published_at?->toIso8601String(),
        ])->values()->all()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'subject_id' => $schema->string()
                ->description('O id da disciplina (uuid) — vem de disciplinas_liberadas ou minha_progressao.')
                ->required(),
        ];
    }
}
