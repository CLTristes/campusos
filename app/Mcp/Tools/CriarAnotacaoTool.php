<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use CampusOs\Core\Actions\Input\McpInputAdapter;
use CampusOs\Lifeos\Actions\CreateNoteAction;
use CampusOs\Lifeos\Http\Resources\NoteResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * A ÚNICA tool de escrita — e por isso a única que checa uma habilidade de
 * token separada (`mcp:write`), além do `mcp:read` que a rota já exige de
 * todas. Igual ao acervo REST: nasce sempre `private`, `CreateNoteAction`
 * ignora qualquer visibilidade que a IA mande — nada de regra nova aqui, só
 * tradução (Fase 1 do desenho).
 */
#[Description('Cria uma anotação pessoal (resumo, dica, prova antiga, lista resolvida) — nasce sempre privada; publicar pro acervo é um passo separado, pelo app.')]
final class CriarAnotacaoTool extends Tool
{
    protected string $name = 'criar_anotacao';

    public function handle(Request $request): Response|ResponseFactory
    {
        if (! auth()->user()->tokenCan('mcp:write')) {
            throw new AuthorizationException('Este token do copiloto não tem permissão de escrita. Gere um novo token com escrita habilitada em Configurações.');
        }

        $note = (new CreateNoteAction)->execute([
            'author_id' => auth()->id(),
            ...McpInputAdapter::from($request->all()),
        ]);

        return Response::structured(['data' => NoteResource::make($note->load(['author', 'term']))->resolve()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('O título da anotação.')->required(),
            'body_md' => $schema->string()->description('O conteúdo, em markdown.')->required(),
            'kind' => $schema->string()
                ->description('O tipo: summary (resumo), past_exam (prova antiga), solved_exercise_list (lista resolvida), material ou tip (dica).')
                ->enum(['summary', 'past_exam', 'solved_exercise_list', 'material', 'tip'])
                ->required(),
            'subject_id' => $schema->string()->description('A disciplina, se houver (uuid).'),
        ];
    }
}
