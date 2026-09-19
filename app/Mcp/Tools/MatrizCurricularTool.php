<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Catalog\Http\Resources\CurriculumResource;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

/**
 * Adaptador fino sobre `CurriculumResource` — o MESMO formato de
 * `GET /api/v1/courses/{course}/curriculum`, só resolvendo o curso a partir
 * do vínculo do aluno em vez de um `{course}` na URL. Diferente de
 * `disciplinas_liberadas` (o que falta PRA ELE agora): aqui é a matriz
 * inteira, todo período, útil pra "o que tem no 5º período" ou "quais são as
 * optativas" sem depender do progresso individual.
 */
#[Description('A matriz curricular inteira do curso do aluno: todas as disciplinas por período, com pré-requisitos, carga horária e os grupos de optativas — não só o que falta pra ele. Use para perguntas sobre a estrutura do curso, não sobre o progresso dele.')]
final class MatrizCurricularTool extends Tool
{
    use ResolvesRegistration;

    protected string $name = 'matriz_curricular';

    public function handle(Request $request): Response|ResponseFactory
    {
        $registration = $this->resolveRegistration(auth()->user());

        $curriculum = $registration->curriculum()
            ->with(['electiveGroups', 'complementaryCategories', 'curriculumSubjects.subject', 'curriculumSubjects.electiveGroup', 'curriculumSubjects.prerequisites.required.subject'])
            ->firstOrFail();

        return Response::structured(['data' => CurriculumResource::make($curriculum)->resolve()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
