<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Lifeos\Models\Note;
use CampusOs\Lifeos\Models\NoteVote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Voto positivo — a curadoria contra slop do acervo (B5 esticada). Um
 * clique alterna: quem ainda não votou passa a votar, quem já votou desfaz.
 * Nunca dois estados — não existe downvote, só presença ou ausência do voto
 * (a mesma decisão de produto por trás de `criar_anotacao` nascer sempre
 * privada: o menor mecanismo que resolve o problema real).
 *
 * `nte_upvotes_count` é mantido na MESMA transação do voto — nunca um job
 * separado, nunca um `COUNT` ao vivo em `acervo_da_disciplina`/
 * `buscar_anotacoes`, que rodam a cada pergunta ao copiloto.
 */
final class ToggleNoteVoteAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'note_id' => ['required', 'string', 'uuid'],
            'user_id' => ['required', 'string', 'uuid'],
        ];
    }

    /** @param array<string, mixed> $data @return array{note: Note, voted: bool} */
    protected function handle(array $data): array
    {
        $note = Note::query()->findOrFail($data['note_id']);

        if ($note->author_usr_id === $data['user_id']) {
            throw ValidationException::withMessages([
                'note_id' => 'Você não pode votar na própria anotação.',
            ]);
        }

        return DB::transaction(function () use ($note, $data): array {
            $existing = NoteVote::query()
                ->where('note_nte_id', $note->nte_id)
                ->where('user_usr_id', $data['user_id'])
                ->first();

            if ($existing !== null) {
                $existing->delete();
                $note->decrement('nte_upvotes_count');

                return ['note' => $note->refresh(), 'voted' => false];
            }

            NoteVote::query()->create([
                'note_nte_id' => $note->nte_id,
                'user_usr_id' => $data['user_id'],
            ]);
            $note->increment('nte_upvotes_count');

            return ['note' => $note->refresh(), 'voted' => true];
        });
    }
}
