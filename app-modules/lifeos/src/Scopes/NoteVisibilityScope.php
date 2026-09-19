<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Scopes;

use CampusOs\Core\Contracts\EnrolledSubjectsProvider;
use CampusOs\Lifeos\Enums\Visibility;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * O coração do desafio 5.2. Uma nota é visível se QUALQUER uma valer:
 *
 *   author_usr_id = usuário atual                          (sempre)
 *   nte_visibility = institution                            (mesmo tenant — já garantido pelo EntityScope)
 *   nte_visibility = course     AND o usuário tem vínculo naquele curso
 *   nte_visibility = subject    AND o usuário JÁ CURSOU OU CURSA a disciplina — EM QUALQUER TERMO
 *   nte_visibility = offering   AND o usuário cursou AQUELA oferta
 *
 * A linha do `subject` é o desafio inteiro. Repare no que NÃO está nela:
 * nenhuma condição sobre `term_trm_id`.
 *
 * `subject` usa o contrato `EnrolledSubjectsProvider` (fronteira sagrada com o
 * `journey`); `course`/`offering` leem `config('models.registration')` e
 * `config('models.subject_enrollment')` direto — são consultas por FK simples,
 * sem a regra de negócio de "atravessar termo" que justifica um contrato.
 */
final class NoteVisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if ($user === null) {
            // Sem usuário autenticado (CLI/job), nada de "visível a mim":
            // nte_id é NOT NULL, então isto sempre devolve zero linhas.
            $builder->whereNull($model->getTable().'.nte_id');

            return;
        }

        self::constrain($builder, $user);
    }

    public static function constrain(Builder $builder, Authenticatable $user): Builder
    {
        $userId = (string) $user->getAuthIdentifier();

        return $builder->where(function (Builder $q) use ($userId): void {
            $q->where('author_usr_id', $userId)
                ->orWhere('nte_visibility', Visibility::Institution->value)
                ->orWhere(fn (Builder $qq) => $qq
                    ->where('nte_visibility', Visibility::Course->value)
                    ->whereIn('course_crs_id', self::courseIdsFor($userId)))
                ->orWhere(fn (Builder $qq) => $qq
                    ->where('nte_visibility', Visibility::Subject->value)
                    ->whereIn('subject_sbj_id', self::subjectIdsFor($userId)))
                ->orWhere(fn (Builder $qq) => $qq
                    ->where('nte_visibility', Visibility::Offering->value)
                    ->whereIn('offering_ofr_id', self::offeringIdsFor($userId)));
        });
    }

    /** @return list<string> */
    private static function subjectIdsFor(string $userId): array
    {
        return app(EnrolledSubjectsProvider::class)->subjectIdsFor($userId);
    }

    /** @return list<string> */
    private static function courseIdsFor(string $userId): array
    {
        $registrationModel = config('models.registration');

        return $registrationModel::query()
            ->whereHas('student', fn ($q) => $q->where('user_usr_id', $userId))
            ->pluck('course_crs_id')
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<string> */
    private static function offeringIdsFor(string $userId): array
    {
        $registrationModel = config('models.registration');
        $enrollmentModel = config('models.subject_enrollment');

        $registrationIds = $registrationModel::query()
            ->whereHas('student', fn ($q) => $q->where('user_usr_id', $userId))
            ->pluck('reg_id');

        return $enrollmentModel::query()
            ->whereIn('registration_reg_id', $registrationIds)
            ->whereNotNull('offering_ofr_id')
            ->pluck('offering_ofr_id')
            ->unique()
            ->values()
            ->all();
    }
}
