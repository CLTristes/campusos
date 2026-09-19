<?php

declare(strict_types=1);

namespace CampusOs\Core\Contracts;

/**
 * As quatro perguntas do painel da coordenação (B8, desafio 5.1) — agregados
 * ANÔNIMOS, nunca aluno nomeado (ver docs/dominio/PAINEL_COORDENACAO.md).
 *
 * O contrato vive no `core` e a implementação no `journey`: `insights` nunca
 * lê `subject_enrollments`/`registrations` direto — mesma fronteira sagrada
 * de `EnrolledSubjectsProvider`. `$courseId` nulo significa "a instituição
 * inteira" (escopo de `institution_admin`); preenchido, "só este curso"
 * (escopo de `coordinator`) — a implementação nunca decide sozinha qual dos
 * dois: quem resolve isso é o controller, a partir do usuário autenticado,
 * nunca de um parâmetro vindo do cliente.
 */
interface AcademicStatsProvider
{
    /**
     * Onde a turma empaca: taxa de reprovação por disciplina, com nota e
     * falta separadas, nos últimos `$lastTerms` termos que tiveram matrícula.
     * Categorias com menos de 5 tentativas resolvidas não aparecem (piso de
     * anonimato).
     *
     * @return list<array{subject_code: string, subject_name: string, total_attempts: int, failed_grade: int, failed_absence: int, failed_both: int, failure_rate: float}>
     */
    public function failureRateBySubject(?string $courseId, int $lastTerms): array;

    /**
     * Quanto custa: atraso médio (em termos além do previsto pela matriz)
     * entre os vínculos ATIVOS de cada coorte de ingresso. Coortes com menos
     * de 5 vínculos ativos não aparecem (piso de anonimato).
     *
     * @return list<array{entry_term: string, active_registrations: int, avg_delay_terms: float}>
     */
    public function cohortDelay(?string $courseId): array;

    /**
     * Quem está perto do limite: quantidade de vínculos ativos cuja previsão
     * de formatura (mesmo cálculo de `ProgressReadModel::forecast`) cabe em
     * `$within` termos ou menos até o prazo de integralização da matriz.
     * Um número só — sem piso de anonimato próprio, é sempre o curso/
     * instituição inteira, nunca um recorte menor.
     */
    public function registrationsNearDeadline(?string $courseId, int $within): int;

    /**
     * O que está represado: quantos vínculos ativos já estão elegíveis
     * (mesmo cálculo de `EligibilityReadModel::nextTerm`) para cada
     * disciplina ainda não cursada — o dado direto de planejamento de oferta.
     * Disciplinas com menos de 5 elegíveis não aparecem (piso de anonimato).
     *
     * @return list<array{subject_code: string, subject_name: string, demand: int}>
     */
    public function demandForNextTerm(?string $courseId): array;
}
