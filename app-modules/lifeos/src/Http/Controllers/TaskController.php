<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Http\Controllers;

use CampusOs\Lifeos\Actions\AdoptTaskAction;
use CampusOs\Lifeos\Actions\CreateTaskAction;
use CampusOs\Lifeos\Actions\UpdateTaskStatusAction;
use CampusOs\Lifeos\Http\Resources\TaskResource;
use CampusOs\Lifeos\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @group Tarefas da turma
 *
 * B6 do desafio 5.2: uma pessoa cadastra um prazo na oferta, a turma inteira
 * enxerga na própria agenda (`GET /me/agenda`) e decide se adota.
 */
final class TaskController
{
    /**
     * Criar tarefa
     *
     * Ao contrário da nota, a tarefa não nasce sempre privada: informe
     * `visibility: offering` + `offering_id` para já cadastrar direto na
     * turma.
     *
     * @authenticated
     *
     * @bodyParam title string required Example: Prova 2
     * @bodyParam description_md string Conteúdo em markdown.
     * @bodyParam kind string required exam, assignment, reading ou personal. Example: exam
     * @bodyParam due_at string Data limite (ISO 8601). Example: 2026-10-14T23:59:00-03:00
     * @bodyParam visibility string private, offering, subject, course ou institution. Default: private.
     * @bodyParam subject_id string A disciplina, se houver.
     * @bodyParam offering_id string A turma — obrigatório se visibility=offering.
     *
     * @response 201 scenario="criada na turma" {"data":{"id":"01a0…","title":"Prova 2","visibility":"offering"}}
     */
    public function store(Request $request, CreateTaskAction $action): JsonResponse
    {
        $task = $action->execute([
            'owner_id' => $request->user()->usr_id,
            ...$request->only(['title', 'description_md', 'kind', 'due_at', 'visibility', 'subject_id', 'offering_id']),
        ]);

        return TaskResource::make($task->load('owner'))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Adotar tarefa da turma
     *
     * Cria uma cópia privada apontando pra origem — sem afetar a tarefa
     * original nem quem mais adotou.
     *
     * @authenticated
     *
     * @response 200 scenario="adotada" {"data":{"id":"01a0…","visibility":"private","origin_task_id":"01a0…"}}
     * @response 403 scenario="não compartilhada" {"message":"Esta tarefa não foi compartilhada com a turma."}
     */
    public function adopt(Request $request, Task $task, AdoptTaskAction $action): JsonResponse
    {
        $copy = $action->execute([
            'user_id' => $request->user()->usr_id,
            'task_id' => $task->tsk_id,
        ]);

        return TaskResource::make($copy->load('owner'))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Mudar o status
     *
     * Só o dono da tarefa (a cópia adotada é sua desde a adoção).
     *
     * @authenticated
     *
     * @bodyParam status string required todo, doing ou done. Example: done
     *
     * @response 200 scenario="concluída" {"data":{"id":"01a0…","status":"done"}}
     * @response 403 scenario="não é o dono" {"message":"Esta tarefa não é sua."}
     */
    public function updateStatus(Request $request, Task $task, UpdateTaskStatusAction $action): TaskResource
    {
        $this->assertOwner($request, $task);

        $updated = $action->execute([
            'task_id' => $task->tsk_id,
            'status' => $request->string('status')->toString(),
        ]);

        return TaskResource::make($updated->load('owner'));
    }

    private function assertOwner(Request $request, Task $task): void
    {
        if ($task->owner_usr_id !== $request->user()->usr_id) {
            throw new AccessDeniedHttpException('Esta tarefa não é sua.');
        }
    }
}
