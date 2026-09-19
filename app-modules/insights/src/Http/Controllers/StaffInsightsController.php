<?php

declare(strict_types=1);

namespace CampusOs\Insights\Http\Controllers;

use CampusOs\Core\Contracts\AcademicStatsProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @group Painel da coordenação
 *
 * B8, segunda esticada do desafio 5.1: agregados ANÔNIMOS pra quem coordena
 * — nunca aluno nomeado (ver docs/dominio/PAINEL_COORDENACAO.md). `insights`
 * não tem tabela própria: tudo aqui vem de `AcademicStatsProvider`, contrato
 * do `core` implementado no `journey`.
 *
 * O escopo (curso × instituição) é resolvido AQUI, a partir do usuário
 * autenticado — nunca de um `course_id` que o cliente mande. Coordenador
 * enxerga o próprio curso (`usr.course_crs_id`); `institution_admin` enxerga
 * a instituição inteira (`courseId: null`, o `EntityScope` já faz o resto).
 */
final class StaffInsightsController
{
    /**
     * Onde a turma empaca
     *
     * Taxa de reprovação por disciplina, nota e falta separadas, nos últimos
     * `last_terms` termos com matrícula. Disciplinas com menos de 5
     * tentativas resolvidas não aparecem (piso de anonimato).
     *
     * @authenticated
     *
     * @queryParam last_terms integer Quantos termos recentes considerar. Default: 4. Example: 4
     *
     * @response 200 scenario="com gargalo" {"data":[{"subject_code":"MAT029","subject_name":"Cálculo 2","total_attempts":40,"failed_grade":15,"failed_absence":6,"failed_both":0,"failure_rate":0.525}]}
     */
    public function bottlenecks(Request $request, AcademicStatsProvider $stats): JsonResponse
    {
        $lastTerms = (int) $request->integer('last_terms', 4);

        return response()->json(['data' => $stats->failureRateBySubject($this->resolveCourseId($request), $lastTerms)]);
    }

    /**
     * Quanto custa
     *
     * Atraso médio (em termos além do previsto pela matriz) entre os
     * vínculos ativos de cada coorte de ingresso. Coortes com menos de 5
     * vínculos ativos não aparecem (piso de anonimato).
     *
     * @authenticated
     *
     * @response 200 scenario="com atraso" {"data":[{"entry_term":"2022/1","active_registrations":38,"avg_delay_terms":2.4}]}
     */
    public function cohorts(Request $request, AcademicStatsProvider $stats): JsonResponse
    {
        return response()->json(['data' => $stats->cohortDelay($this->resolveCourseId($request))]);
    }

    /**
     * Quem está perto do limite
     *
     * Quantidade de vínculos ativos cuja previsão de formatura cabe em
     * `within` termos ou menos até o prazo de integralização.
     *
     * @authenticated
     *
     * @queryParam within integer Quantos termos até o prazo contam como "perto". Default: 2. Example: 2
     *
     * @response 200 scenario="ok" {"data":{"count":12,"within_terms":2}}
     */
    public function atRisk(Request $request, AcademicStatsProvider $stats): JsonResponse
    {
        $within = (int) $request->integer('within', 2);

        return response()->json(['data' => [
            'count' => $stats->registrationsNearDeadline($this->resolveCourseId($request), $within),
            'within_terms' => $within,
        ]]);
    }

    /**
     * O que está represado
     *
     * Quantos vínculos ativos já estão elegíveis para cada disciplina ainda
     * não cursada — planejamento de oferta direto, sem chute. Disciplinas
     * com menos de 5 elegíveis não aparecem (piso de anonimato).
     *
     * @authenticated
     *
     * @response 200 scenario="com demanda" {"data":[{"subject_code":"ES52C","subject_name":"Engenharia de Software II","demand":22}]}
     */
    public function demand(Request $request, AcademicStatsProvider $stats): JsonResponse
    {
        return response()->json(['data' => $stats->demandForNextTerm($this->resolveCourseId($request))]);
    }

    /**
     * Coordenador → o próprio curso (nunca o `course_id` do request).
     * Institution_admin → `null` (a instituição inteira, via `EntityScope`).
     */
    private function resolveCourseId(Request $request): ?string
    {
        $user = $request->user();

        if ($user->usr_role->value !== 'coordinator') {
            return null;
        }

        if ($user->course_crs_id === null) {
            throw new UnprocessableEntityHttpException('Coordenador sem curso configurado — fale com a gestão da instituição.');
        }

        return $user->course_crs_id;
    }
}
