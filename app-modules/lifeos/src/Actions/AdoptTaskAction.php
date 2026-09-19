<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Task;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Replica, nunca compartilha. Adotar uma tarefa da turma cria uma CÓPIA
 * apontando pra origem (`origin_tsk_id`) — sem isso, o primeiro que marcasse
 * como feita marcaria pra turma inteira. A cópia é sempre `private`/`todo`:
 * a partir do momento em que adota, cada aluno gerencia a própria (Fase 3,
 * regra 1 do desenho do acervo compartilhado).
 *
 * `firstOrCreate` casa com o índice único `(owner_usr_id, origin_tsk_id)`:
 * duplo clique em "adotar" devolve a MESMA cópia, nunca cria uma segunda.
 */
final class AdoptTaskAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'uuid'],
            'task_id' => ['required', 'string', 'uuid'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): Task
    {
        $origin = Task::query()->findOrFail($data['task_id']);

        if ($origin->tsk_visibility === Visibility::Private) {
            throw new AccessDeniedHttpException('Esta tarefa não foi compartilhada com a turma.');
        }

        if ($origin->owner_usr_id === $data['user_id']) {
            throw ValidationException::withMessages([
                'task_id' => 'Você já é o dono desta tarefa.',
            ]);
        }

        return Task::query()->firstOrCreate(
            [
                'owner_usr_id' => $data['user_id'],
                'origin_tsk_id' => $origin->tsk_id,
            ],
            [
                'tsk_title' => $origin->tsk_title,
                'tsk_description_md' => $origin->tsk_description_md,
                'tsk_kind' => $origin->tsk_kind,
                'tsk_due_at' => $origin->tsk_due_at,
                'subject_sbj_id' => $origin->subject_sbj_id,
                'offering_ofr_id' => $origin->offering_ofr_id,
                'tsk_status' => TaskStatus::Todo,
                'tsk_visibility' => Visibility::Private,
            ],
        );
    }
}
