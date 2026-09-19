<?php

declare(strict_types=1);

namespace CampusOs\Journey\ReadModels;

use CampusOs\Journey\Models\Registration;
use Illuminate\Support\Collection;

/**
 * O que libera e o que trava (Fase 5) + a simulação de reprovação (Fase 6) —
 * a última esticada de melhor retorno do desafio 5.1
 * (docs-site/features/matricula-e-progressao.html).
 *
 * Puro cálculo sobre dado que já existe — nenhuma tabela nova. `journey` nunca
 * importa classe do `catalog`: os objetos de curriculum_subjects/prerequisites
 * chegam por relação Eloquent dinâmica (mesmo padrão de `ProgressReadModel`),
 * e o enum `prq_type` é comparado pelo `->value`, nunca pela classe.
 *
 * **Período atual é uma simplificação deliberada**: o número de semestres
 * reais distintos que o aluno já cursou (`termsAttended`, o mesmo cálculo que
 * `ProgressReadModel::forecast` já faz) — NÃO o déficit de CHS acumulado do
 * documento (`docs/dominio/DOCUMENTOS_ACADEMICOS.md` §3). Validado contra o
 * histórico real: bate exatamente com o "Período: 8" impresso. A regra
 * completa soma obrigatórias E optativas no déficit; a parte de optativas
 * exigiria atribuir cada matrícula de eletiva a um "slot" de período da
 * matriz — informação que este schema não modela e que não dava para
 * reconstruir com segurança a partir de um único histórico real (ver
 * docs/dominio/PROGRESSAO.md pendência 4).
 *
 * A fronteira `journey`×`catalog` para "esta oferta pertence a este curso" é
 * simples o bastante (FK direta) para não precisar de contrato — mesmo
 * raciocínio já registrado em `AgendaReadModel`/`NoteVisibilityScope`.
 */
final class EligibilityReadModel
{
    /**
     * Fase 5 — travadas com o motivo, na fronteira ATUAL do aluno.
     *
     * @return array<string, mixed>
     */
    public function nextTerm(Registration $registration): array
    {
        $enrollments = $registration->subjectEnrollments()->with('subject')->get();

        return $this->evaluate($registration, $enrollments);
    }

    /**
     * Fase 6 — clona o cenário marcando os códigos de `$failSubjectCodes`
     * como reprovados (removidos do conjunto aprovado) e recalcula a
     * "primeira oportunidade" de cada disciplina pendente sobre o MESMO
     * grafo de pré-requisitos — sem escrita no banco, sem Action.
     *
     * @param  list<string>  $failSubjectCodes
     * @return array<string, mixed>
     */
    public function simulate(Registration $registration, array $failSubjectCodes): array
    {
        $enrollments = $registration->subjectEnrollments()->with('subject')->get();

        $subjectModel = config('models.subject');
        $failedSubjectIds = $subjectModel::query()
            ->whereIn('sbj_code', $failSubjectCodes)
            ->pluck('sbj_id')
            ->all();

        [$byId, $currentPeriod] = $this->loadGraph($registration, $enrollments);

        $approvedReal = $this->approvedSubjectIds($enrollments);
        $approvedSimulated = array_values(array_diff($approvedReal, $failedSubjectIds));

        $realPeriods = $this->earliestPeriods($byId, $approvedReal, $currentPeriod);
        $simulatedPeriods = $this->earliestPeriods($byId, $approvedSimulated, $currentPeriod);

        // A própria disciplina reprovada nunca entra em "afetadas" — ela é a
        // ENTRADA da simulação, não um efeito colateral em cascata. O que
        // interessa mostrar é quem DEPENDE dela (direta ou transitivamente).
        $changes = [];
        foreach ($byId as $cbsId => $cs) {
            if (in_array($cs->subject_sbj_id, $failedSubjectIds, true)) {
                continue;
            }

            if ($simulatedPeriods[$cbsId] > $realPeriods[$cbsId]) {
                $changes[] = [
                    'subject' => ['code' => $cs->subject->sbj_code, 'name' => $cs->subject->sbj_name],
                    'real_earliest_period' => $realPeriods[$cbsId],
                    'simulated_earliest_period' => $simulatedPeriods[$cbsId],
                    'delay_terms' => $simulatedPeriods[$cbsId] - $realPeriods[$cbsId],
                ];
            }
        }

        usort($changes, fn (array $a, array $b): int => $b['delay_terms'] <=> $a['delay_terms']);

        return [
            'failed' => $failSubjectCodes,
            'current_period' => $currentPeriod,
            'affected_subjects' => $changes,
            // O maior atraso observado entre as disciplinas AFETADAS — não um
            // max() global sobre a matriz inteira, que uma disciplina alheia
            // à cadeia (ex.: um minimum_term fixo) mascararia nos dois
            // cenários igualmente.
            'estimated_graduation_delay_terms' => $changes === [] ? 0 : max(array_column($changes, 'delay_terms')),
        ];
    }

    /** @param  Collection<int, \CampusOs\Journey\Models\SubjectEnrollment>  $enrollments @return array<string, mixed> */
    private function evaluate(Registration $registration, Collection $enrollments): array
    {
        [$byId, $currentPeriod] = $this->loadGraph($registration, $enrollments);

        $approvedIds = $this->approvedSubjectIds($enrollments);
        $enrolledIds = $enrollments
            ->filter(fn ($e): bool => $e->sen_status->isInProgress())
            ->pluck('subject_sbj_id')
            ->unique()
            ->all();
        $totalApprovedHours = (int) $enrollments
            ->filter(fn ($e): bool => $e->sen_status->countsAsCompleted())
            ->sum(fn ($e): int => $e->sen_hours_earned ?: (int) $e->subject->sbj_hours);

        $eligible = [];
        $blocked = [];

        foreach ($byId as $cs) {
            // Já cumprida ou em curso agora: não é "próximo período".
            if (in_array($cs->subject_sbj_id, $approvedIds, true) || in_array($cs->subject_sbj_id, $enrolledIds, true)) {
                continue;
            }

            $reasons = $this->blockedReasons($cs, $approvedIds, $enrolledIds, $currentPeriod, $totalApprovedHours);
            $entry = ['code' => $cs->subject->sbj_code, 'name' => $cs->subject->sbj_name];

            if ($reasons === []) {
                $eligible[] = $entry;
            } else {
                $blocked[] = [...$entry, 'blocked_by' => $reasons];
            }
        }

        return [
            'current_period' => $currentPeriod,
            'eligible' => $eligible,
            'blocked' => $blocked,
        ];
    }

    /**
     * As razões de UMA disciplina pendente estar travada AGORA — checagem
     * direta contra o estado atual, sem projeção de período nenhuma.
     *
     * @param  list<string>  $approvedIds
     * @param  list<string>  $enrolledIds
     * @return list<array<string, mixed>>
     */
    private function blockedReasons($curriculumSubject, array $approvedIds, array $enrolledIds, int $currentPeriod, int $totalApprovedHours): array
    {
        $reasons = [];

        foreach ($curriculumSubject->prerequisites as $prerequisite) {
            $type = $prerequisite->prq_type->value;

            if ($type === 'subject' && $prerequisite->required !== null) {
                if (! in_array($prerequisite->required->subject_sbj_id, $approvedIds, true)) {
                    $reasons[] = [
                        'type' => 'subject',
                        'subject' => ['code' => $prerequisite->required->subject->sbj_code, 'name' => $prerequisite->required->subject->sbj_name],
                        'reason' => "Precisa ter aprovado {$prerequisite->required->subject->sbj_name}.",
                    ];
                }
            } elseif ($type === 'corequisite' && $prerequisite->required !== null) {
                $satisfied = in_array($prerequisite->required->subject_sbj_id, $approvedIds, true)
                    || in_array($prerequisite->required->subject_sbj_id, $enrolledIds, true);

                if (! $satisfied) {
                    $reasons[] = [
                        'type' => 'corequisite',
                        'subject' => ['code' => $prerequisite->required->subject->sbj_code, 'name' => $prerequisite->required->subject->sbj_name],
                        'reason' => "Precisa cursar junto com (ou já ter aprovado) {$prerequisite->required->subject->sbj_name}.",
                    ];
                }
            } elseif ($type === 'minimum_term' && $prerequisite->prq_min_term !== null && $currentPeriod < $prerequisite->prq_min_term) {
                $reasons[] = [
                    'type' => 'minimum_term',
                    'subject' => null,
                    'reason' => "Exige estar a partir do {$prerequisite->prq_min_term}º período (você está no {$currentPeriod}º).",
                ];
            } elseif ($type === 'minimum_hours' && $prerequisite->prq_min_hours !== null && $totalApprovedHours < $prerequisite->prq_min_hours) {
                $faltam = $prerequisite->prq_min_hours - $totalApprovedHours;
                $reasons[] = [
                    'type' => 'minimum_hours',
                    'subject' => null,
                    'reason' => "Faltam {$faltam}h para o mínimo de {$prerequisite->prq_min_hours}h.",
                ];
            }
        }

        return $reasons;
    }

    /**
     * Longest-path sobre o grafo de pré-requisitos: a primeira oportunidade
     * de cada disciplina, dado um conjunto de aprovadas. `minimum_hours` não
     * é projetado pra frente (não dá pra saber QUAIS disciplinas renderão
     * quantas horas em qual período sem simular o curso inteiro) — fica de
     * fora do cálculo, ressalva honesta no relatório.
     *
     * @param  Collection<string, mixed>  $byId
     * @param  list<string>  $approvedIds
     * @return array<string, int>
     */
    private function earliestPeriods(Collection $byId, array $approvedIds, int $currentPeriod): array
    {
        $memo = [];

        foreach ($byId as $cbsId => $cs) {
            $visiting = [];
            $this->earliestPeriod($cbsId, $byId, $approvedIds, $currentPeriod, $memo, $visiting);
        }

        return $memo;
    }

    /**
     * @param  Collection<string, mixed>  $byId
     * @param  list<string>  $approvedIds
     * @param  array<string, int>  $memo
     * @param  array<string, bool>  $visiting
     */
    private function earliestPeriod(string $cbsId, Collection $byId, array $approvedIds, int $currentPeriod, array &$memo, array &$visiting): int
    {
        if (isset($memo[$cbsId])) {
            return $memo[$cbsId];
        }

        $cs = $byId->get($cbsId);

        if ($cs === null) {
            return $currentPeriod + 1;
        }

        if (in_array($cs->subject_sbj_id, $approvedIds, true)) {
            return $memo[$cbsId] = $currentPeriod;
        }

        // Ciclo em pré-requisito existe na vida real (erro de cadastro no
        // colegiado) — protegido com o conjunto de visitados, nunca recursão
        // sem guarda.
        if (isset($visiting[$cbsId])) {
            return $currentPeriod + 1;
        }

        $visiting[$cbsId] = true;
        $earliest = $currentPeriod + 1;

        foreach ($cs->prerequisites as $prerequisite) {
            $type = $prerequisite->prq_type->value;

            if ($type === 'subject' && $prerequisite->required_cbs_id !== null) {
                $earliest = max($earliest, $this->earliestPeriod($prerequisite->required_cbs_id, $byId, $approvedIds, $currentPeriod, $memo, $visiting) + 1);
            } elseif ($type === 'corequisite' && $prerequisite->required_cbs_id !== null) {
                $earliest = max($earliest, $this->earliestPeriod($prerequisite->required_cbs_id, $byId, $approvedIds, $currentPeriod, $memo, $visiting));
            } elseif ($type === 'minimum_term' && $prerequisite->prq_min_term !== null) {
                $earliest = max($earliest, (int) $prerequisite->prq_min_term);
            }
        }

        unset($visiting[$cbsId]);

        return $memo[$cbsId] = $earliest;
    }

    /**
     * @param  Collection<int, \CampusOs\Journey\Models\SubjectEnrollment>  $enrollments
     * @return array{0: Collection<string, mixed>, 1: int}
     */
    private function loadGraph(Registration $registration, Collection $enrollments): array
    {
        $registration->loadMissing('curriculum');

        $byId = $registration->curriculum->curriculumSubjects()
            ->with(['subject', 'prerequisites.required.subject'])
            ->get()
            ->keyBy('cbs_id');

        // Período atual — ver ressalva no cabeçalho da classe.
        $currentPeriod = $enrollments->pluck('term_trm_id')->unique()->count();

        return [$byId, $currentPeriod];
    }

    /**
     * @param  Collection<int, \CampusOs\Journey\Models\SubjectEnrollment>  $enrollments
     * @return list<string>
     */
    private function approvedSubjectIds(Collection $enrollments): array
    {
        return $enrollments
            ->filter(fn ($e): bool => $e->sen_status->countsAsCompleted())
            ->pluck('subject_sbj_id')
            ->unique()
            ->values()
            ->all();
    }
}
