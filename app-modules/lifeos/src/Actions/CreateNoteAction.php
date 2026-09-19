<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;

/**
 * Uma nota nasce SEMPRE privada — regra de ouro do acervo (Fase 5 do desenho):
 * publicar é ato deliberado, nunca automático. Mesmo que o cliente mande outra
 * visibilidade aqui, ela é ignorada; só `UpdateNoteVisibilityAction` publica.
 */
final class CreateNoteAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'author_id' => ['required', 'string', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'body_md' => ['required', 'string'],
            'kind' => ['required', 'string', 'in:summary,past_exam,solved_exercise_list,material,tip'],
            'subject_id' => ['nullable', 'string', 'uuid'],
            'offering_id' => ['nullable', 'string', 'uuid'],
            'course_id' => ['nullable', 'string', 'uuid'],
            'term_id' => ['nullable', 'string', 'uuid'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): Note
    {
        return Note::query()->create([
            'author_usr_id' => $data['author_id'],
            'nte_title' => $data['title'],
            'nte_body_md' => $data['body_md'],
            'nte_kind' => $data['kind'],
            'subject_sbj_id' => $data['subject_id'] ?? null,
            'offering_ofr_id' => $data['offering_id'] ?? null,
            'course_crs_id' => $data['course_id'] ?? null,
            'term_trm_id' => $data['term_id'] ?? null,
            'nte_visibility' => Visibility::Private,
        ]);
    }
}
