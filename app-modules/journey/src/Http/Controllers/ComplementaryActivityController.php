<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Controllers;

use CampusOs\Journey\Actions\CreateComplementaryActivityAction;
use CampusOs\Journey\Http\Resources\ComplementaryActivityResource;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\ReadModels\ComplementaryHoursReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group Horas complementares
 *
 * O tracker pessoal do aluno para atividades complementares (B7) — teto por
 * categoria e certificado guardado. Puramente informativo: não muda
 * `GET /me/progress` (ver docs/dominio/HORAS_COMPLEMENTARES.md).
 */
final class ComplementaryActivityController
{
    /**
     * Minhas atividades complementares
     *
     * A lista mais o resumo por categoria (`summary`): quanto foi declarado,
     * quanto conta depois do teto, e quanto foi perdido.
     *
     * @authenticated
     */
    public function index(Request $request, ComplementaryHoursReadModel $hours): JsonResponse
    {
        $registration = $this->resolveRegistration($request);

        $activities = $registration->complementaryActivities()
            ->with('category')
            ->orderByDesc('cac_created_at')
            ->get();

        return response()->json([
            'data' => ComplementaryActivityResource::collection($activities),
            'summary' => $hours->summaryFor($registration),
        ]);
    }

    /**
     * Declarar atividade complementar
     *
     * Sem fila de homologação nesta entrega — o contador anda na hora, com
     * `capped`/`hours_not_counted` avisando se a categoria estourou o teto.
     *
     * @authenticated
     *
     * @bodyParam category_id string required Example: 01a0b823-b7d7-72d8-8db9-810d0e28d9c7
     * @bodyParam title string required Example: Semana Acadêmica de Sistemas de Informação
     * @bodyParam hours_claimed integer required Example: 20
     * @bodyParam issued_at string Data do certificado (ISO 8601).
     * @bodyParam certificate file O certificado (PDF, PNG ou JPG).
     *
     * @response 201 scenario="dentro do teto" {"data":{"id":"01a0…","hours_claimed":20},"capped":false,"hours_not_counted":0}
     * @response 201 scenario="estourou o teto" {"data":{"id":"01a0…","hours_claimed":46},"capped":true,"hours_not_counted":46}
     */
    public function store(Request $request, CreateComplementaryActivityAction $action, ComplementaryHoursReadModel $hours): JsonResponse
    {
        $registration = $this->resolveRegistration($request);

        $activity = $action->execute([
            'registration_id' => $registration->reg_id,
            ...$request->only(['category_id', 'title', 'hours_claimed', 'issued_at']),
            'certificate' => $request->file('certificate'),
        ]);

        $summary = $hours->summaryForCategory($registration, $activity->complementary_category_ccg_id);

        return ComplementaryActivityResource::make($activity->load('category'))
            ->additional([
                'capped' => $summary['capped'] ?? false,
                'hours_not_counted' => $summary['hours_not_counted'] ?? 0,
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mesmo lookup de `ProgressController::me` — o vínculo mais recente do
     * usuário autenticado. Sem vínculo, não há matriz pra conferir teto
     * nenhum.
     */
    private function resolveRegistration(Request $request): Registration
    {
        $registration = Registration::query()
            ->whereRelation('student', 'user_usr_id', $request->user()->usr_id)
            ->orderByDesc('reg_created_at')
            ->first();

        if ($registration === null) {
            throw new NotFoundHttpException(
                'Você ainda não tem vínculo acadêmico. Envie seu histórico escolar.'
            );
        }

        return $registration;
    }
}
