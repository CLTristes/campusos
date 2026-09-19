<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Core\Actions\Input\McpInputAdapter;
use CampusOs\Journey\Actions\SimulateFailureAction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `SimulateFailureAction` — o mesmo cálculo de
 * `POST /api/v1/me/simulate`. Sem escrita no banco (a própria Action existe
 * só pela validação de entrada — ver comentário nela), então é leitura,
 * mesmo vindo de um verbo POST do lado REST.
 */
#[Description('E se eu reprovar em X? Simula o impacto em cascata de uma ou mais reprovações hipotéticas sobre a mesma matriz de pré-requisitos — sem gravar nada. Use quando o aluno perguntar "o que acontece se eu reprovar em...".')]
final class SimularReprovacaoTool extends Tool
{
    use ResolvesRegistration;

    protected string $name = 'simular_reprovacao';

    public function handle(Request $request): Response|ResponseFactory
    {
        $registration = $this->resolveRegistration(auth()->user());
        $data = McpInputAdapter::from($request->all());

        $result = (new SimulateFailureAction)->execute([
            'registration_id' => $registration->reg_id,
            'fail' => $data['fail'] ?? null,
        ]);

        return Response::structured(['data' => $result]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [
            'fail' => $schema->array()
                ->items($schema->string())
                ->description('Os códigos das disciplinas a simular como reprovadas (ex.: ["MAT034"]).')
                ->required(),
        ];
    }
}
