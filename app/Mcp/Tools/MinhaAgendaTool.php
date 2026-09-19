<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use CampusOs\Lifeos\ReadModels\AgendaReadModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `AgendaReadModel::for` — o mesmo read model de
 * `GET /api/v1/me/agenda`.
 */
#[Description('A agenda do aluno: pendências próprias e o que a turma compartilhou nas ofertas do termo corrente, ainda não adotado — ordenado por prazo.')]
final class MinhaAgendaTool extends Tool
{
    protected string $name = 'minha_agenda';

    public function handle(Request $request): Response|ResponseFactory
    {
        return Response::structured(['data' => (new AgendaReadModel)->for(auth()->user())]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
