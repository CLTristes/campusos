<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Http\Controllers;

use CampusOs\Lifeos\Actions\CreateNoteAction;
use CampusOs\Lifeos\Actions\ToggleNoteVoteAction;
use CampusOs\Lifeos\Actions\UpdateNoteVisibilityAction;
use CampusOs\Lifeos\Http\Resources\NoteResource;
use CampusOs\Lifeos\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @group Acervo do veterano
 *
 * O caderno pessoal do aluno (organizador do 5.1) e o acervo que atravessa
 * turmas (o 5.2) são o MESMO recurso — o que muda é só `visibility`. Toda
 * leitura já passa pelo `NoteVisibilityScope`: o que este controller devolve é
 * exatamente o que o usuário autenticado tem direito de ver, sem `if` nenhum
 * aqui.
 */
final class NoteController
{
    /**
     * Listar notas
     *
     * As próprias (qualquer visibilidade) mais o que outros compartilharam com
     * este usuário, pela escada de visibilidade. Filtre por `subject_id` para
     * ver o acervo de uma disciplina específica — é a tela do 5.2.
     *
     * @authenticated
     *
     * @queryParam subject_id string O acervo de uma disciplina específica. Example: 01a0b823-b7d7-72d8-8db9-810d0e28d9c7
     *
     * @response 200 scenario="acervo de uma disciplina" {"data":[{"id":"01a0…","title":"Resumo da P2","kind":"summary","visibility":"subject","upvotes_count":3,"author":{"id":"01a0…","name":"Um Veterano"},"term":{"year":2024,"period":1}}]}
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return NoteResource::collection(
            Note::query()
                ->with(['author', 'term'])
                ->with(['votes' => fn ($q) => $q->where('user_usr_id', $request->user()->usr_id)])
                ->when($request->query('subject_id'), fn ($q, $subjectId) => $q->where('subject_sbj_id', $subjectId))
                // Mais votada primeiro — a curadoria contra slop (B5 esticada).
                // Empate desfaz por data, como sempre foi.
                ->orderByDesc('nte_upvotes_count')
                ->orderByRaw('COALESCE(nte_published_at, nte_created_at) DESC')
                ->get()
        );
    }

    /**
     * Criar nota
     *
     * Nasce privada — publicar é um passo separado (`.../visibility`).
     *
     * @authenticated
     *
     * @bodyParam title string required Example: Resumo da P2
     * @bodyParam body_md string required Conteúdo em markdown.
     * @bodyParam kind string required summary, past_exam, solved_exercise_list, material ou tip. Example: summary
     * @bodyParam subject_id string A disciplina, se houver. Example: 01a0b823-b7d7-72d8-8db9-810d0e28d9c7
     * @bodyParam offering_id string A turma, se houver.
     * @bodyParam course_id string O curso, se houver.
     * @bodyParam term_id string O semestre em que foi escrita, se houver.
     *
     * @response 201 scenario="criada" {"data":{"id":"01a0…","title":"Resumo da P2","visibility":"private"}}
     */
    public function store(Request $request, CreateNoteAction $action): JsonResponse
    {
        $note = $action->execute([
            'author_id' => $request->user()->usr_id,
            ...$request->only(['title', 'body_md', 'kind', 'subject_id', 'offering_id', 'course_id', 'term_id']),
        ]);

        return NoteResource::make($note->load(['author', 'term']))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Ver uma nota
     *
     * @authenticated
     */
    public function show(Note $note): NoteResource
    {
        return NoteResource::make($note->load(['author', 'term']));
    }

    /**
     * Mudar a visibilidade
     *
     * Publicar (subir na escada) ou despublicar (voltar para `private`, que
     * sempre funciona, sem pré-condição nenhuma). Só o autor pode chamar.
     *
     * @authenticated
     *
     * @bodyParam visibility string required private, offering, subject, course ou institution. Example: subject
     * @bodyParam subject_id string Obrigatório se a nota ainda não tiver disciplina e o alvo exigir uma.
     *
     * @response 200 scenario="publicada" {"data":{"id":"01a0…","visibility":"subject"}}
     * @response 403 scenario="não é o autor" {"message":"Esta nota não é sua."}
     */
    public function updateVisibility(Request $request, Note $note, UpdateNoteVisibilityAction $action): NoteResource
    {
        $this->assertAuthor($request, $note);

        $updated = $action->execute([
            'note_id' => $note->nte_id,
            ...$request->only(['visibility', 'subject_id', 'offering_id', 'course_id']),
        ]);

        return NoteResource::make($updated->load(['author', 'term']));
    }

    /**
     * Votar / desfazer voto
     *
     * Alterna: primeira chamada vota, segunda desfaz. Só quem enxerga a nota
     * (mesmo `NoteVisibilityScope` de sempre) e não é o autor pode votar.
     *
     * @authenticated
     *
     * @response 200 scenario="votou" {"data":{"id":"01a0…","upvotes_count":4},"voted":true}
     * @response 422 scenario="a própria nota" {"message":"Você não pode votar na própria anotação."}
     */
    public function vote(Request $request, Note $note, ToggleNoteVoteAction $action): JsonResponse
    {
        $result = $action->execute([
            'note_id' => $note->nte_id,
            'user_id' => $request->user()->usr_id,
        ]);

        return NoteResource::make($result['note'])
            ->additional(['voted' => $result['voted']])
            ->response();
    }

    /**
     * O scope já garante que só quem enxerga a nota chega até aqui (senão é
     * 404 de model binding). Falta só distinguir "enxergo porque é minha" de
     * "enxergo porque foi compartilhada comigo" — só o primeiro pode mudar a
     * visibilidade.
     */
    private function assertAuthor(Request $request, Note $note): void
    {
        if ($note->author_usr_id !== $request->user()->usr_id) {
            throw new AccessDeniedHttpException('Esta nota não é sua.');
        }
    }
}
