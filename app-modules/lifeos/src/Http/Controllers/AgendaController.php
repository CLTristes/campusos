<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Http\Controllers;

use CampusOs\Lifeos\ReadModels\AgendaReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Tarefas da turma
 */
final class AgendaController
{
    /**
     * Minha agenda
     *
     * Pendências próprias mais as tarefas que a turma compartilhou nas
     * ofertas em que você está matriculado **no termo corrente**, ainda não
     * adotadas (`adopted: false`) — ordenado por prazo.
     *
     * @authenticated
     *
     * @response 200 scenario="com tarefa da turma pendente de adoção" {"data":[{"id":"01a0…","title":"Prova 2","due_at":"2026-10-14T23:59:00-03:00","visibility":"offering","adopted":false}]}
     */
    public function me(Request $request, AgendaReadModel $agenda): JsonResponse
    {
        return response()->json(['data' => $agenda->for($request->user())]);
    }
}
