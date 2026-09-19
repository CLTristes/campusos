<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Models\Task;

/**
 * "Cada um gerencia a própria" (Fase 3, regra 1): mudar o status é sempre na
 * PRÓPRIA cópia. A autoria é conferida no controller (mesmo padrão de
 * `NoteController::assertAuthor`) — nunca aqui, pra manter a Action pura.
 */
final class UpdateTaskStatusAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'task_id' => ['required', 'string', 'uuid'],
            'status' => ['required', 'string', 'in:todo,doing,done'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): Task
    {
        $task = Task::query()->findOrFail($data['task_id']);
        $task->update(['tsk_status' => TaskStatus::from($data['status'])]);

        return $task->refresh();
    }
}
