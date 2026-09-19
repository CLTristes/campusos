<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Controllers;

use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\ReadModels\AcademicHistoryReadModel;
use CampusOs\Journey\ReadModels\AdminDashboardReadModel;
use CampusOs\Journey\ReadModels\ComplementaryHoursReadModel;
use CampusOs\Journey\ReadModels\EligibilityReadModel;
use CampusOs\Journey\ReadModels\ProgressReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @group Painel administrativo
 *
 * Diferente de `StaffInsightsController` (agregado ANÔNIMO, `insights`,
 * `MIN_COHORT=5`): aqui o aluno é nomeado — o mesmo dado que `/data-console`
 * já mostra tabela por tabela, só agregado numa lista e num "aluno 360" que
 * reaproveita os MESMOS read models de `GET /me/progress`, `meu_historico`
 * (copiloto MCP) e `GET /me/next-term`, sem duplicar cálculo nenhum.
 *
 * O escopo curso × instituição é o MESMO de `StaffInsightsController`:
 * coordenador enxerga o próprio curso, `institution_admin` a instituição
 * inteira. Nenhuma tool MCP expõe isto — é canal exclusivamente REST, porque
 * o copiloto fala com o ALUNO sobre a própria jornada, nunca com um gestor
 * sobre a jornada de terceiros.
 */
final class StaffStudentController
{
    /**
     * Painel — os números agregados
     *
     * @authenticated
     *
     * @response 200 scenario="ok" {"data":{"total_students":42,"total_registrations":42,"students_by_course":[{"course":"Bacharelado em Sistemas de Informação","count":42}],"document_requests_by_status":[{"status":"parsed","status_label":"Aguardando sua conferência","total":3}],"average_progress_percentage":38.4}}
     */
    public function dashboard(Request $request, AdminDashboardReadModel $readModel): JsonResponse
    {
        return response()->json(['data' => $readModel->summary($this->resolveCourseId($request))]);
    }

    /**
     * Todos os alunos
     *
     * Uma linha por vínculo (o mesmo aluno com dois cursos aparece duas
     * vezes — é o que `EligibilityReadModel`/`ProgressReadModel` já tratam
     * como unidade). `progress`/`at_risk` vêm do MESMO `ProgressReadModel`
     * de `GET /me/progress`.
     *
     * @authenticated
     *
     * @response 200 scenario="ok" {"data":[{"registration_id":"01a0…","student_name":"Felipe Kurt Pohling","registration_number":"2567857","course":"Bacharelado em Sistemas de Informação","campus":"Francisco Beltrão","entry_term":"1/2023","completed_hours":2540,"required_hours":3000,"percentage":84.7,"terms_attended":8,"at_risk":false}]}
     */
    public function index(Request $request): JsonResponse
    {
        $registrations = Registration::query()
            ->with(['student.user', 'course.campus', 'entryTerm', 'curriculum'])
            ->when($this->resolveCourseId($request), fn ($q, $courseId) => $q->where('course_crs_id', $courseId))
            ->orderBy('reg_created_at')
            ->get();

        $rows = $registrations->map(function (Registration $registration): array {
            $progress = (new ProgressReadModel)->for($registration);

            return [
                'registration_id' => $registration->reg_id,
                'student_name' => $registration->student->std_name,
                'registration_number' => $registration->reg_number,
                'course' => $registration->course->crs_name,
                'campus' => $registration->course->campus?->cps_name,
                'entry_term' => $registration->entryTerm->label(),
                'completed_hours' => $progress['overall']['completed_hours'],
                'required_hours' => $progress['overall']['required_hours'],
                'percentage' => $progress['overall']['percentage'],
                'terms_attended' => $progress['forecast']['terms_attended'],
                'at_risk' => $progress['forecast']['at_risk'],
            ];
        });

        return response()->json(['data' => $rows->values()]);
    }

    /**
     * Um aluno — progresso, histórico, horas e o que libera
     *
     * O "aluno 360": junta `ProgressReadModel`, `AcademicHistoryReadModel`
     * (o mesmo de `meu_historico` no copiloto MCP), `ComplementaryHoursReadModel`
     * e `EligibilityReadModel::nextTerm` — zero cálculo novo, só composição.
     *
     * @authenticated
     *
     * @urlParam registration string required O id do vínculo. Example: 01a0b897-1404-7199-b64e-62d10c74c9c0
     */
    public function show(
        Request $request,
        Registration $registration,
        AcademicHistoryReadModel $history,
        ComplementaryHoursReadModel $hours,
        EligibilityReadModel $eligibility,
    ): JsonResponse {
        $courseId = $this->resolveCourseId($request);

        if ($courseId !== null && $registration->course_crs_id !== $courseId) {
            throw new AccessDeniedHttpException('Este aluno não é do seu curso.');
        }

        $registration->loadMissing(['student.user', 'course.campus', 'entryTerm']);

        return response()->json(['data' => [
            'student' => [
                'registration_id' => $registration->reg_id,
                'name' => $registration->student->std_name,
                'email' => $registration->student->user?->usr_email,
                'registration_number' => $registration->reg_number,
                'course' => $registration->course->crs_name,
                'campus' => $registration->course->campus?->cps_name,
                'entry_term' => $registration->entryTerm->label(),
            ],
            'progress' => (new ProgressReadModel)->for($registration),
            'history' => $history->forRegistration($registration),
            'complementary_hours' => $hours->summaryFor($registration)->values(),
            'next_term' => $eligibility->nextTerm($registration),
        ]]);
    }

    /**
     * Coordenador → o próprio curso (nunca o `course_id` do request).
     * Institution_admin → `null` (a instituição inteira, via `EntityScope`).
     * Mesma regra de `StaffInsightsController::resolveCourseId`.
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
