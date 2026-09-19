<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Controllers;

use CampusOs\Journey\Actions\SimulateFailureAction;
use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\ReadModels\EligibilityReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group Elegibilidade e simulação
 *
 * B8, primeira esticada do desafio 5.1: "compreensão dos pré-requisitos",
 * "planejamento dos próximos períodos" e "impactos de reprovações" — puro
 * cálculo sobre o histórico e a matriz que já existem, nenhuma tabela nova.
 */
final class EligibilityController
{
    /**
     * O que libera e o que trava
     *
     * As disciplinas que o aluno ainda não cursa nem cumpriu, separadas em
     * liberadas (pode pegar já) e travadas — cada travada com o motivo
     * explícito, não só a etiqueta.
     *
     * @authenticated
     *
     * @response 200 scenario="com travas" {"data":{"current_period":6,"eligible":[{"code":"ES52C","name":"Engenharia de Software II"}],"blocked":[{"code":"ES62A","name":"Compiladores","blocked_by":[{"type":"subject","subject":{"code":"LIP301","name":"Linguagens Formais"},"reason":"Precisa ter aprovado Linguagens Formais."}]}]}}
     */
    public function nextTerm(Request $request, EligibilityReadModel $eligibility): JsonResponse
    {
        $registration = $this->resolveRegistration($request);

        return response()->json(['data' => $eligibility->nextTerm($registration)]);
    }

    /**
     * E se eu reprovar? — simulação
     *
     * Sem escrita no banco: clona o histórico marcando as disciplinas
     * informadas como reprovadas e recalcula a primeira oportunidade de
     * cada disciplina pendente sobre o mesmo grafo de pré-requisitos.
     *
     * @authenticated
     *
     * @bodyParam fail string[] required Os códigos das disciplinas a simular como reprovadas. Example: ["MAT034"]
     *
     * @response 200 scenario="com impacto em cascata" {"data":{"failed":["MAT034"],"current_period":6,"affected_subjects":[{"subject":{"code":"WBE501","name":"Desenvolvimento Web Back-End"},"real_earliest_period":6,"simulated_earliest_period":7,"delay_terms":1}],"estimated_graduation_delay_terms":1}}
     */
    public function simulate(Request $request, SimulateFailureAction $action): JsonResponse
    {
        $registration = $this->resolveRegistration($request);

        $result = $action->execute([
            'registration_id' => $registration->reg_id,
            'fail' => $request->input('fail'),
        ]);

        return response()->json(['data' => $result]);
    }

    /**
     * Mesmo lookup de `ProgressController::me` — o vínculo mais recente do
     * usuário autenticado.
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
