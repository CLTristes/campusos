<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Task;
use Illuminate\Validation\ValidationException;

/**
 * Ao contrário de `CreateNoteAction`, uma tarefa NÃO nasce sempre privada:
 * "Prova 2 — 14/10" pode nascer direto em `offering` — cadastrar já É o ato
 * de compartilhar com a turma (Fase 3 do desenho do acervo compartilhado).
 *
 * Mesma guarda de `UpdateNoteVisibilityAction`, porém na criação: pedir
 * `offering`/`subject` sem a FK correspondente deixaria a tarefa "da turma"
 * sem turma nenhuma — nenhum `whereIn` da agenda bateria.
 */
final class CreateTaskAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'owner_id' => ['required', 'string', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'description_md' => ['nullable', 'string'],
            'kind' => ['required', 'string', 'in:exam,assignment,reading,personal'],
            'due_at' => ['nullable', 'date'],
            'visibility' => ['nullable', 'string', 'in:private,offering,subject,course,institution'],
            'subject_id' => ['nullable', 'string', 'uuid'],
            'offering_id' => ['nullable', 'string', 'uuid'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): Task
    {
        $visibility = Visibility::from($data['visibility'] ?? Visibility::Private->value);

        if ($visibility->requiresOffering() && ($data['offering_id'] ?? null) === null) {
            throw ValidationException::withMessages([
                'offering_id' => 'Informe a turma antes de compartilhar a tarefa com ela.',
            ]);
        }

        if ($visibility->requiresSubject() && ($data['subject_id'] ?? null) === null) {
            throw ValidationException::withMessages([
                'subject_id' => 'Informe a disciplina antes de compartilhar a tarefa.',
            ]);
        }

        return Task::query()->create([
            'owner_usr_id' => $data['owner_id'],
            'tsk_title' => $data['title'],
            'tsk_description_md' => $data['description_md'] ?? null,
            'tsk_kind' => $data['kind'],
            'tsk_due_at' => $data['due_at'] ?? null,
            'tsk_status' => TaskStatus::Todo,
            'tsk_visibility' => $visibility,
            'subject_sbj_id' => $data['subject_id'] ?? null,
            'offering_ofr_id' => $data['offering_id'] ?? null,
        ]);
    }
}
