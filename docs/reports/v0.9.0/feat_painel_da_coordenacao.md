# 📦 Documento de Entrega — CampusOS · v0.9.0

## Painel da coordenação (B8, segunda esticada) — desafio 5.1

> **Data:** 19/09/2026
> **Branch:** `feat/painel-da-coordenacao`
> **Escopo:** B8 completo (Fases 1–2 de `docs-site/features/painel-da-coordenacao.html`) — as QUATRO perguntas do desenho, não só as duas mínimas do "se o tempo acabar". Nenhuma tabela nova (`insights` continua sem tabela própria).
> **Qualidade:** 201 testes / 616 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

A coordenação enxerga onde a turma empaca, quanto isso atrasa a formatura por coorte, quem está perto do limite e o que está represado pro próximo período — tudo em agregados anônimos, nunca aluno nomeado, escopados ao próprio curso do coordenador (nunca ao que o cliente manda).

## 0 — A lacuna que o desenho não previa: escopo do coordenador

O desenho pede "coordenador enxerga o próprio curso", mas `users` só tinha `campus_cps_id` — nenhuma coluna liga um coordenador a um curso específico. Perguntei ao dono do produto antes de tocar numa tabela core (`users`): adicionar `course_crs_id` nullable (mesmo padrão de `campus_cps_id`) — um curso só, porque nenhuma coordenação multi-curso existe no piloto. `institution_admin` nunca preenche a coluna: enxerga a instituição inteira via `EntityScope`, sem precisar de curso.

## 1 — O contrato: `insights` nunca lê `subject_enrollments` direto

`AcademicStatsProvider` (4 métodos) vive no `core`; `JourneyAcademicStatsProvider` implementa no `journey`, bindado em `JourneyServiceProvider` — mesmo padrão de `EnrolledSubjectsProvider`. `$courseId` nulo = a instituição inteira; preenchido = só aquele curso. Quem decide qual dos dois é o **controller**, a partir de `$request->user()`, nunca de um `course_id` no request.

## 2 — Duas perguntas reaproveitam read models já prontos, em vez de reimplementar

`registrationsNearDeadline` reaproveita `ProgressReadModel::forecast.terms_until_deadline`; `demandForNextTerm` reaproveita `EligibilityReadModel::nextTerm`. Nenhuma das duas fórmulas foi duplicada em SQL — duplicá-las criaria uma segunda versão de "quando o aluno se forma"/"o que ele pode pegar" que divergiria da primeira na próxima mudança de regra. As outras duas (`failureRateBySubject`, `cohortDelay`) agregam em SQL puro (`DB::table()` com `JOIN`/`GROUP BY`/`HAVING`) — a exceção legítima à regra "Eloquent é a única camada de dados".

## 3 — Bug real pego por teste antes do merge: `DB::table()` não aplica `EntityScope`

A primeira versão de `failureRateBySubject`/`cohortDelay` esqueceu de filtrar `entity_ent_id` — como são queries em `DB::table()` (query builder cru), o global scope do Eloquent **não se aplica**. Com o escopo de `institution_admin` (`courseId: null`), isso agregaria a tabela inteira, **de todas as instituições**, não só a de quem perguntou. Um teste dedicado (`"institution_admin nunca enxerga o gargalo nem a coorte de OUTRA instituição"`) pegou antes do merge; a lição foi documentada em `docs/arquitetura/IMPLEMENTACAO.md` como FAQ geral, porque o padrão de falha é traiçoeiro (não quebra nada em dev com um tenant só).

## 4 — Piso de anonimato, aplicado no read model

`MIN_COHORT = 5`: disciplinas com menos de 5 tentativas resolvidas, coortes com menos de 5 vínculos ativos, e disciplinas com menos de 5 elegíveis não aparecem — filtrado no `JourneyAcademicStatsProvider`, nunca só escondido na interface (esconder no front é vazamento com passo extra, o JSON continua lá).

## 5 — RBAC de borda, genérico

`EnsureUserHasRole` (`app/Http/Middleware`) — `role:coordinator,institution_admin` — é o primeiro middleware de papel do sistema, escrito genérico (aceita qualquer lista de papéis) em vez de acoplado a "insights", pra servir qualquer rota futura sem precisar de uma classe nova por capacidade.

## 6 — O que existe agora

| Rota | O que faz |
| --- | --- |
| `GET /api/v1/staff/insights/bottlenecks` | Reprovação por disciplina, nota e falta separadas |
| `GET /api/v1/staff/insights/cohorts` | Atraso médio por coorte de ingresso |
| `GET /api/v1/staff/insights/at-risk` | Quantos vínculos estão a N termos do prazo |
| `GET /api/v1/staff/insights/demand` | Quantos vínculos já estão elegíveis pra cada disciplina pendente |

28 endpoints no total agora, 21 tabelas (`users` ganhou uma coluna, nenhuma tabela nova).

## Ressalvas honestas

1. **`cohortDelay` usa a mesma aproximação de "período atual" já registrada em `EligibilityReadModel` (B8 esticada 1)** — `termsAttended`, não o déficit de CHS completo do documento (obrigatórias+optativas). Só considera vínculos ATIVOS, não formados/desistentes — "atraso até agora", não "atraso até a formatura" histórico.
2. **`registrationsNearDeadline`/`demandForNextTerm` rodam em loop PHP por vínculo ativo** (reaproveitando read models existentes), não uma query agregada nova. Aceitável na escala do piloto; se a base crescer, merece pré-computar/cachear em vez de recalcular por request — registrado como decisão em `docs/dominio/PAINEL_COORDENACAO.md`.
3. **RBAC granular por permissão** (tabela `permissions`, como no FibroMais) segue ◇ planejado — hoje permissão É o papel.
4. **Sem tela**, a prova roda por Pest (9 testes dedicados, incluindo isolamento de tenant e de curso) e por `curl`/Postman.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | `insights` deixa de ser esqueleto; `tenancy` ganha `users.course_crs_id`; 28 endpoints (Parte X); 201/616 (Parte IX); Parte XII perde a linha do painel |
| `CLAUDE.md` | Mapa de módulos: `insights` vira "em código" (as 4 perguntas); §Estado atual |

Já feito nesta entrega (barato, não esperou o `/sync-docs`): `docs/dominio/PAINEL_COORDENACAO.md` criado; `docs/README.md`, `docs/dominio/PROGRESSAO.md` (cross-link), `docs/arquitetura/BANCO.md` (users.course_crs_id) e `docs/arquitetura/IMPLEMENTACAO.md` (FAQ do `DB::table()`×`EntityScope`) já atualizados; `docs-site/features/painel-da-coordenacao.html` e `docs-site/plano-de-ataque.html` já refletem ✅ em código.
