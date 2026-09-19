<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Journey\ReadModels\AcademicHistoryReadModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `AcademicHistoryReadModel` — não existia read model
 * nenhum pra isto (nem endpoint REST); as outras seis tools de leitura só
 * agregam ou filtram pendente. Fechou uma lacuna real: sem esta tool não dava
 * pra conferir o que o sistema gravou disciplina por disciplina.
 */
#[Description('O histórico completo do aluno, disciplina por disciplina: situação, nota, frequência e horas — inclusive o que está cursando agora. Use quando o aluno perguntar por uma disciplina específica do passado, ou desconfiar de algum dado da progressão.')]
final class MeuHistoricoTool extends Tool
{
    use ResolvesRegistration;

    protected string $name = 'meu_historico';

    public function handle(Request $request): Response|ResponseFactory
    {
        $registration = $this->resolveRegistration(auth()->user());

        return Response::structured(['data' => (new AcademicHistoryReadModel)->forRegistration($registration)]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
