# Painel da coordenação — agregado, nunca nominal

> **Área:** `insights` (lê por contrato) + `journey` (implementa) · **Estado:** ATIVO (MVP) · **Última sincronização:** v0.9.0 · 2026-09-19

## O problema de negócio

O desafio 5.1 lista onde o problema ocorre: coordenações de curso, secretaria
acadêmica, departamentos, colegiados — nenhum desses é o aluno. Um produto que
só atende o estudante resolve metade do enunciado. Todo coordenador sabe, por
conversa de corredor, que "a turma empaca em Cálculo 2"; poucos conseguem
dizer o número, e quase nenhum consegue dizer quanto isso custa em atraso de
formatura — porque o dado existe espalhado em relatórios que ninguém cruza.

Desenho completo em
[`docs-site/features/painel-da-coordenacao.html`](../../docs-site/features/painel-da-coordenacao.html).

## As regras

1. **`insights` não tem tabela própria.** Lê por contrato declarado no `core`
   (`AcademicStatsProvider`) e implementado no `journey`
   (`JourneyAcademicStatsProvider`) — ler `subject_enrollments`/`registrations`
   direto de dentro do `insights` furaria a fronteira modular.
2. **Agregação em SQL, não em PHP.** É a exceção legítima à regra "Eloquent é
   a única camada de dados": `DB::table()` com `JOIN`/`GROUP BY`/`HAVING`
   continua sendo query estruturada, nunca string montada com valor do
   cliente. Trazer todas as `subject_enrollments` pra somar em PHP seria o
   erro real — não acontece aqui.
3. **`DB::table()` não aplica `EntityScope`.** É Eloquent quem aplica o global
   scope de tenant automaticamente — quem usa o query builder cru (como as
   duas primeiras perguntas do painel) precisa filtrar `entity_ent_id`
   explicitamente por `TenantContext::id()`. Esquecer isso vazaria a
   instituição inteira quando o escopo for `institution_admin`
   (`$courseId = null`) — bug real, pego pelo próprio teste antes do merge,
   não só teoria.
4. **Nenhum endpoint devolve aluno nomeado.** "68 alunos a menos de 240h de
   formar" é gestão; a lista dos 68 com nome é outra coisa, exige outra
   justificativa e não entra aqui.
5. **Piso de anonimato: `MIN_COHORT = 5`.** Recorte com menos de 5
   vínculos/tentativas/elegíveis não é exibido — numa turma de 6, "33% de
   reprovação" identifica duas pessoas para quem conhece a turma. Aplicado
   no read model, nunca só escondido na interface (esconder no front é
   vazamento com passo extra — o JSON continua lá).
6. **Recorte por escopo, resolvido no servidor.** Coordenador enxerga o
   próprio curso (`users.course_crs_id`); `institution_admin` enxerga a
   instituição inteira (`$courseId = null`, o `EntityScope` já filtra o
   resto). Nunca a partir de um `course_id` que o cliente manda — aceitar o
   recorte do request é como um painel de gestão vira leitura de curso
   alheio.
7. **O acervo não entra no painel.** Quem publicou quantas anotações não é
   métrica de gestão — transformar colaboração em vigilância mata a
   colaboração (desafio 5.2). Nenhuma das quatro perguntas do painel toca
   `notes`.
8. **RBAC de borda, papel genérico.** `role:coordinator,institution_admin`
   (`EnsureUserHasRole`, `app/Http/Middleware`) é reutilizável pra qualquer
   rota futura que precise checar `usr_role` — não é específico de
   `insights`.

## As quatro perguntas (e a simplificação de cada uma)

| Pergunta | Método | Como é calculado |
| --- | --- | --- |
| Onde a turma empaca | `failureRateBySubject` | Por disciplina, nos últimos N termos com matrícula: nota e falta separadas. Denominador = tentativas com desfecho REAL (aprovado ou reprovado) — crédito consignado e dispensa não contam como tentativa. |
| Quanto custa | `cohortDelay` | Por coorte de ingresso, só vínculos ATIVOS: `max(0, termsAttended - cur_expected_terms)`, em média. **Não é** o déficit de CHS completo do documento (ver `PROGRESSAO.md` regra 10/pendência 4) — é o mesmo tipo de aproximação já usada em `EligibilityReadModel`, aplicada aqui a um agregado por coorte em vez de "período atual" de UM aluno. |
| Quem está perto do limite | `registrationsNearDeadline` | Reaproveita `ProgressReadModel::forecast.terms_until_deadline` — roda por vínculo ativo (nunca reimplementa a fórmula, uma fonte só). |
| O que está represado | `demandForNextTerm` | Reaproveita `EligibilityReadModel::nextTerm` — conta quantos vínculos ativos já estão `eligible` para cada disciplina ainda não cursada. |

## Decisões e porquês

| Decisão | Alternativas consideradas | Por que esta | Quando revisitar |
| --- | --- | --- | --- |
| `users.course_crs_id` nullable, um curso só | Instituição inteira pra coordenador também (sem coluna nova); tabela pivot `course_coordinators` | O piloto não tem coordenação multi-curso — a coluna nullable resolve hoje sem inventar uma tabela pra um caso que não existe | Se aparecer coordenador de mais de um curso, virar pivot sem quebrar esta coluna |
| `registrationsNearDeadline`/`demandForNextTerm` reaproveitam `ProgressReadModel`/`EligibilityReadModel` por vínculo (loop em PHP), não uma query agregada nova | Reimplementar a lógica de previsão/elegibilidade em SQL puro | As duas fórmulas já existem, testadas, como fonte única de verdade — duplicá-las em SQL criaria uma SEGUNDA versão de "quando o aluno se forma"/"o que ele pode pegar" que divergiria da primeira na próxima mudança de regra | Se a base crescer a ponto do loop pesar (não é o caso do piloto), pré-computar/cachear em vez de recalcular por request |
| `cohortDelay` só considera vínculos ATIVOS (não formados/trancados/desligados) | Incluir formados, usando `reg_graduated_at` pra estimar o atraso real até a formatura | `reg_graduated_at` é uma data, não um termo — mapear pra "quantos termos levou" exigiria a mesma reconstrução incerta já descartada em `PROGRESSAO.md` pendência 4. Escopo em ativos é honesto: mede o atraso JÁ ACUMULADO por quem ainda está cursando | Se o produto precisar de "atraso até formar" histórico, não "atraso até agora" |
| `role` é um middleware genérico de lista de papéis, não uma checagem semântica (`UserRole::seesInsights()`) | Um middleware dedicado chamando o método semântico do enum | Já existe `seesInsights()` pra outros usos (ex.: UI decidir o que mostrar); o middleware genérico serve qualquer rota futura sem precisar de uma classe nova por capacidade | Se as duas fontes de verdade (a lista literal na rota e o método do enum) divergirem um dia |

## ⚠️ Pendências do dono do produto

1. **RBAC granular por permissão** (tabela `permissions`, como no FibroMais)
   é ◇ planejado — hoje permissão É o papel (`UserRole`), documentado desde
   o enum.
2. **`cohortDelay` não conta coortes formadas ou desistentes** — só ativas.
   Se a coordenação quiser "atraso médio até a formatura" (histórico, não
   projeção), é uma pergunta nova, não uma extensão trivial desta.

## Mapa de código

| Regra | Onde vive | Teste |
| --- | --- | --- |
| 1 | `core/src/Contracts/AcademicStatsProvider.php` + `journey/src/Support/JourneyAcademicStatsProvider.php` | `ArchTest` — "Insights não importa o interno de outro módulo" |
| 2 | `journey/src/Support/JourneyAcademicStatsProvider.php` (todos os métodos) | `PainelDaCoordenacaoTest` |
| 3 | idem — filtro `entity_ent_id` explícito em `failureRateBySubject`/`cohortDelay` | `PainelDaCoordenacaoTest` — "institution_admin nunca enxerga... de OUTRA instituição" |
| 4, 7 | Ausência de qualquer campo nominal/de `notes` no contrato e nos Resources | — (garantido pela ausência do campo) |
| 5 | `JourneyAcademicStatsProvider::MIN_COHORT` | `PainelDaCoordenacaoTest` — "só coortes/disciplinas com pelo menos 5..." |
| 6 | `insights/src/Http/Controllers/StaffInsightsController.php::resolveCourseId` | `PainelDaCoordenacaoTest` — "coordenador de um curso nunca vê o gargalo de outro curso" |
| 8 | `app/Http/Middleware/EnsureUserHasRole.php` | `PainelDaCoordenacaoTest` — "aluno recebe 403 em todos os endpoints" |
