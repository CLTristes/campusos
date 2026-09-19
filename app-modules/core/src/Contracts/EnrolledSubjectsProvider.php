<?php

declare(strict_types=1);

namespace CampusOs\Core\Contracts;

/**
 * A pergunta que sustenta o desafio 5.2: em quais disciplinas este usuário já
 * esteve matriculado — em QUALQUER semestre, por QUALQUER vínculo?
 *
 * O contrato vive no `core` e a implementação no `journey`: o `lifeos` nunca lê
 * `subject_enrollments` direto (furaria a fronteira e o ArchTest quebraria).
 * Sem esta pergunta respondida sem o filtro de termo, o acervo do veterano não
 * existe — é por isso que ela ganha um contrato próprio, e não vira só mais uma
 * chave em `config('models.*')`.
 */
interface EnrolledSubjectsProvider
{
    /**
     * @return list<string> ids de subjects que o usuário já cursou ou cursa,
     *                      em qualquer termo, somando todos os vínculos dele
     */
    public function subjectIdsFor(string $userId): array;
}
