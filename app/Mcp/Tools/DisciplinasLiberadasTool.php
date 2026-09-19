<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Journey\ReadModels\EligibilityReadModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `EligibilityReadModel::nextTerm` — o mesmo read model
 * de `GET /api/v1/me/next-term`.
 */
#[Description('O que o aluno pode pegar no próximo período (liberadas), e o que trava cada disciplina pendente — com o motivo explícito, nunca só a etiqueta "travada".')]
final class DisciplinasLiberadasTool extends Tool
{
    use ResolvesRegistration;

    protected string $name = 'disciplinas_liberadas';

    public function handle(Request $request): Response|ResponseFactory
    {
        $registration = $this->resolveRegistration(auth()->user());

        return Response::structured(['data' => (new EligibilityReadModel)->nextTerm($registration)]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
