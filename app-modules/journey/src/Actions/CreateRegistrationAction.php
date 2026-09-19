<?php

declare(strict_types=1);

namespace CampusOs\Journey\Actions;

use CampusOs\Core\Actions\AbstractAction;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Journey\Enums\RegistrationStatus;
use CampusOs\Journey\Models\Registration;
use Illuminate\Validation\ValidationException;

/**
 * Cria o vínculo do aluno com um curso.
 *
 * A matriz NÃO vem do cliente: é resolvida no servidor como a mais recente cujo
 * `cur_effective_from` não é posterior ao termo de ingresso, e fica congelada
 * ali para sempre (docs/dominio/PROGRESSAO.md regras 1 e 2). Deixar o cliente
 * escolher a matriz é deixá-lo escolher o próprio critério de formatura.
 */
final class CreateRegistrationAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'student_id' => ['required', 'string', 'uuid'],
            'course_id' => ['required', 'string', 'uuid'],
            'entry_term_id' => ['required', 'string', 'uuid'],
            'number' => ['required', 'string', 'max:32'],
        ];
    }

    /** @param array<string, mixed> $data */
    protected function authorize(array $data): void
    {
        // Jobs e CLI não têm sessão HTTP — sem contexto, o Entityable não
        // preenche entity_ent_id e o registro nasceria sem dono.
        if (! TenantContext::has()) {
            throw ValidationException::withMessages([
                'entity' => 'Sem contexto de instituição.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    protected function handle(array $data): Registration
    {
        $termModel = config('models.term');
        $curriculumModel = config('models.curriculum');

        $entryTerm = $termModel::query()->findOrFail($data['entry_term_id']);

        $curriculum = $curriculumModel::query()
            ->where('course_crs_id', $data['course_id'])
            ->where(function ($q) use ($entryTerm): void {
                $q->whereNull('cur_effective_from')
                    ->orWhere('cur_effective_from', '<=', $entryTerm->trm_starts_at ?? now());
            })
            ->orderByDesc('cur_effective_from')
            ->first();

        if ($curriculum === null) {
            throw ValidationException::withMessages([
                'course_id' => 'O curso não tem matriz vigente para este semestre de ingresso.',
            ]);
        }

        return Registration::query()->create([
            'student_std_id' => $data['student_id'],
            'course_crs_id' => $data['course_id'],
            'curriculum_cur_id' => $curriculum->cur_id,   // FOTO, nunca reescrita
            'entry_term_trm_id' => $entryTerm->trm_id,
            'reg_number' => $data['number'],
            'reg_status' => RegistrationStatus::Active,
        ]);
    }
}
