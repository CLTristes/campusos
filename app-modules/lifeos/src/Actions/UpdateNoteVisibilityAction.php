<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;
use Illuminate\Validation\ValidationException;

/**
 * Publicar (subir na escada) ou despublicar (voltar para privada).
 *
 * **Despublicar sempre funciona** — nenhuma pré-condição, tira do acervo na
 * hora. **Publicar exige saber PARA QUEM**: subir para `subject` sem a
 * disciplina preenchida deixaria a nota invisível para todo mundo (nenhum
 * `subject_sbj_id` bateria no `whereIn` do scope) — melhor recusar de cara.
 *
 * A autoria é conferida no controller (mesmo padrão de
 * `AcademicDocumentController::assertOwnership`): quem não é autor nem
 * enxerga a nota fora daqui (404 do próprio scope); quem enxerga mas não é
 * autor recebe 403 explícito.
 */
final class UpdateNoteVisibilityAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'note_id' => ['required', 'string', 'uuid'],
            'visibility' => ['required', 'string', 'in:private,offering,subject,course,institution'],
            'subject_id' => ['nullable', 'string', 'uuid'],
            'offering_id' => ['nullable', 'string', 'uuid'],
            'course_id' => ['nullable', 'string', 'uuid'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): Note
    {
        $note = Note::query()->findOrFail($data['note_id']);
        $visibility = Visibility::from($data['visibility']);

        $subjectId = $data['subject_id'] ?? $note->subject_sbj_id;
        $offeringId = $data['offering_id'] ?? $note->offering_ofr_id;
        $courseId = $data['course_id'] ?? $note->course_crs_id;

        if ($visibility->requiresOffering() && $offeringId === null) {
            throw ValidationException::withMessages([
                'offering_id' => 'Informe a turma antes de compartilhar com ela.',
            ]);
        }

        if ($visibility->requiresSubject() && $subjectId === null) {
            throw ValidationException::withMessages([
                'subject_id' => 'Informe a disciplina antes de publicar para quem já a cursou.',
            ]);
        }

        if ($visibility->requiresCourse() && $courseId === null) {
            throw ValidationException::withMessages([
                'course_id' => 'Informe o curso antes de publicar para ele.',
            ]);
        }

        $note->update([
            'nte_visibility' => $visibility,
            'subject_sbj_id' => $subjectId,
            'offering_ofr_id' => $offeringId,
            'course_crs_id' => $courseId,
            // Marca a primeira publicação; despublicar não apaga a data —
            // é histórico de quando a nota passou a existir no acervo.
            'nte_published_at' => $visibility === Visibility::Private ? $note->nte_published_at : ($note->nte_published_at ?? now()),
        ]);

        return $note->refresh();
    }
}
