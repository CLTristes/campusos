<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\ReadModels;

use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Http\Resources\TaskResource;
use CampusOs\Lifeos\Models\Task;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * `GET /me/agenda` — pendências próprias + o que a turma compartilhou,
 * ordenado por prazo. Ao contrário do acervo de notas, aqui o filtro por
 * TERMO existe de propósito: prazo de turma passada é ruído (a nota de uma
 * turma passada é o produto; o prazo de uma turma passada não é nada).
 *
 * A pergunta "em quais ofertas o aluno está matriculado AGORA" é simples o
 * bastante (FK direta, sem a regra de "atravessar termo" que justificou o
 * contrato `EnrolledSubjectsProvider`) — respondida direto via
 * `config('models.*')`, mesmo padrão que `NoteVisibilityScope::offeringIdsFor`
 * já usa hoje. Não criei um método novo no contrato do `core` por isso.
 */
final class AgendaReadModel
{
    /** @return list<array<string, mixed>> */
    public function for(Authenticatable $user): array
    {
        $userId = (string) $user->getAuthIdentifier();

        $own = Task::query()
            ->where('owner_usr_id', $userId)
            ->where('tsk_status', '!=', TaskStatus::Done->value)
            ->with('owner')
            ->get()
            ->map(fn (Task $task): array => $this->format($task, adopted: true));

        $shared = Task::query()
            ->where('tsk_visibility', Visibility::Offering->value)
            ->whereIn('offering_ofr_id', $this->currentOfferingIdsFor($userId))
            ->where('owner_usr_id', '!=', $userId)
            ->whereDoesntHave('adoptions', fn ($q) => $q->where('owner_usr_id', $userId))
            ->with('owner')
            ->get()
            ->map(fn (Task $task): array => $this->format($task, adopted: false));

        return $own->concat($shared)
            ->sortBy(fn (array $t): string => $t['due_at'] ?? '9999-12-31T23:59:59+00:00')
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function format(Task $task, bool $adopted): array
    {
        return [...TaskResource::make($task)->resolve(), 'adopted' => $adopted];
    }

    /**
     * Em quais ofertas este usuário está matriculado no termo CORRENTE —
     * qualquer vínculo, sem herdar a regra "atravessa termo" do acervo.
     *
     * 'current' == `CampusOs\Catalog\Enums\TermStatus::Current`: valor cru
     * (não o enum) para não importar classe de outro módulo (regra de ouro
     * nº 3 — o ArchTest quebraria).
     *
     * @return list<string>
     */
    private function currentOfferingIdsFor(string $userId): array
    {
        $registrationModel = config('models.registration');
        $enrollmentModel = config('models.subject_enrollment');

        $registrationIds = $registrationModel::query()
            ->whereHas('student', fn ($q) => $q->where('user_usr_id', $userId))
            ->pluck('reg_id');

        return $enrollmentModel::query()
            ->whereIn('registration_reg_id', $registrationIds)
            ->whereNotNull('offering_ofr_id')
            ->whereHas('offering.term', fn ($q) => $q->where('trm_status', 'current'))
            ->pluck('offering_ofr_id')
            ->unique()
            ->values()
            ->all();
    }
}
