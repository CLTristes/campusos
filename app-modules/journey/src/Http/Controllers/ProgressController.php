<?php

declare(strict_types=1);

namespace CampusOs\Journey\Http\Controllers;

use CampusOs\Journey\Models\Registration;
use CampusOs\Journey\ReadModels\ProgressReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group Jornada acadêmica
 *
 * A progressão da graduação do aluno autenticado — o endpoint que sozinho
 * desenha a tela principal do produto.
 */
final class ProgressController
{
    /**
     * Minha progressão
     *
     * Quanto falta para formar, por faixa de carga horária, com o que ainda
     * está pendente e a previsão de formatura pelo ritmo **real** do aluno.
     *
     * `overall.required_hours` é **calculado** — obrigatórias + optativas +
     * extensão autônoma — e `extension.counts_in_total` é `false` de propósito:
     * a carga extensionista exigida pelo curso mora *dentro* das disciplinas e
     * é verificada em paralelo. Somá-la inventaria horas que o aluno não
     * precisa cursar.
     *
     * @authenticated
     *
     * @response 404 scenario="sem vínculo" {"message":"Você ainda não tem vínculo acadêmico. Envie seu histórico escolar."}
     */
    public function me(Request $request, ProgressReadModel $progress): JsonResponse
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

        return response()->json(['data' => $progress->for($registration)]);
    }
}
