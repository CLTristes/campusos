<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Journey\ReadModels\ComplementaryHoursReadModel;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `ComplementaryHoursReadModel::summaryFor` — o mesmo
 * read model do resumo de `GET /api/v1/me/complementary-activities`. Nunca
 * soma na progressão (ver docs/dominio/HORAS_COMPLEMENTARES.md) — é só o
 * tracker paralelo, aqui como lá.
 */
#[Description('Horas complementares por categoria: quanto foi declarado, quanto conta depois do teto, e quanto foi perdido por estourar a categoria.')]
final class MinhasHorasTool extends Tool
{
    use ResolvesRegistration;

    protected string $name = 'minhas_horas';

    public function handle(Request $request): Response|ResponseFactory
    {
        $registration = $this->resolveRegistration(auth()->user());

        return Response::structured(['data' => (new ComplementaryHoursReadModel)->summaryFor($registration)->values()->all()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
