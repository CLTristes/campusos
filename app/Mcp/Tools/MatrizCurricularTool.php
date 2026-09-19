<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Tools\Concerns\ResolvesRegistration;
use CampusOs\Catalog\Enums\TermStatus;
use CampusOs\Catalog\Http\Resources\CurriculumResource;
use CampusOs\Catalog\Models\Offering;
use CampusOs\Catalog\Models\Term;
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

        $resource = CurriculumResource::make($curriculum);

        // Sempre o termo `current` — o copiloto fala com o aluno sobre AGORA,
        // nunca aceita um term_id arbitrário do modelo de IA (mesmo raciocínio
        // de disciplinas_liberadas/minha_progressao: nada de parâmetro que
        // deixe a IA escolher de qual termo falar).
        $currentTermId = Term::query()->where('trm_status', TermStatus::Current)->value('trm_id');

        if ($currentTermId !== null) {
            $resource->withAvailability(
                Offering::query()->where('term_trm_id', $currentTermId)->pluck('subject_sbj_id')
            );
        }

        return Response::structured(['data' => $resource->resolve()]);
    }

    /** @return array<string, mixed> */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
