<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Journey\ReadModels\ProgressReadModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `ProgressReadModel` — o mesmo read model de
 * `GET /api/v1/me/progress`. Nenhuma regra nova, nenhum `if` de negócio: se
 * este arquivo precisar de um, a regra pertence à Action/ReadModel, não aqui
 * (ver docs-site/features/copiloto-mcp.html Fase 1).
 */
#[Description('Quanto falta pro aluno se formar: horas cumpridas por faixa, disciplinas pendentes por período, e a previsão de formatura pelo ritmo REAL dele — não pelo ideal da matriz.')]
final class MinhaProgressaoTool extends Tool
{
    use ResolvesRegistration;

    protected string $name = 'minha_progressao';

    public function handle(Request $request): Response|ResponseFactory
    {
        $registration = $this->resolveRegistration(auth()->user());

        return Response::structured(['data' => (new ProgressReadModel)->for($registration)]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
