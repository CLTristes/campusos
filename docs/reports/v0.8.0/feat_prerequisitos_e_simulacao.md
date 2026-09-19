# 📦 Documento de Entrega — CampusOS · v0.8.0

## Pré-requisitos e simulação de reprovação (B8, esticada 1) — desafio 5.1

> **Data:** 19/09/2026
> **Branch:** `feat/prerequisitos-e-simulacao`
> **Escopo:** primeira esticada do B8 (Fases 5–6 de `docs-site/features/matricula-e-progressao.html`) — o que libera/trava e a simulação de reprovação em cascata. Nenhuma tabela nova.
> **Qualidade:** 192 testes / 585 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O aluno vê quais disciplinas pode pegar no próximo período (com o motivo de cada trava) e simula "e se eu reprovar nesta?" — descobrindo, sem escrever nada no banco, quais disciplinas a jusante da cadeia de pré-requisitos atrasariam e por quantos períodos.

## 0 — A investigação que mudou o escopo: o déficit de CHS é mais complexo do que o card descrevia

O desenho original (`docs-site`) e `docs/dominio/PROGRESSAO.md` regra 10 descrevem "período atual" como um déficit de carga horária semanal acumulado (a regra literal do histórico da UTFPR: "16 aulas ou mais de déficit não avança de período"). Antes de codar, li o histórico real do dono do produto e validei essa fórmula contra os números impressos — bate exatamente (déficit final −21,00 CHS, "permanecerá no 8º Período"). Mas a fórmula soma **obrigatórias E optativas**, e a parte de optativas exige atribuir cada matrícula de eletiva aprovada a um "slot" de período da matriz — informação que este schema não modela (uma optativa não carrega o período em que "contou" para o déficit, só o termo real em que foi cursada). Sem um segundo histórico real para desempatar entre hipóteses de atribuição, reconstruir essa parte seria adivinhar uma regra não validável.

**Decisão (perguntada e confirmada com o dono do produto):** "período atual" usa `termsAttended` (o número de semestres reais distintos que o aluno já cursou — o mesmo cálculo que `ProgressReadModel::forecast` já faz) como aproximação. Para o aluno real, isso bate exatamente com o "Período: 8" impresso — mas é coincidência de ele não ter tido trancamento nem lacuna entre semestres, não prova de que a aproximação sempre bate. Ressalva registrada em `docs/dominio/PROGRESSAO.md` pendência 4.

## 1 — `EligibilityReadModel`: quatro tipos de pré-requisito, cada um checado do seu jeito

`prerequisites.prq_type` tem quatro valores (`subject`, `corequisite`, `minimum_hours`, `minimum_term`) — o caso real da matriz 45 é `EST501` (Estágio) exigindo "Período: 5" via `minimum_term`. `journey` nunca importa `CampusOs\Catalog\Enums\PrerequisiteType`: o tipo é sempre comparado pelo `->value` do enum que já vem cast na relação Eloquent — mesmo padrão de `NoteVisibilityScope`/`AgendaReadModel` usando `config('models.*')` para não furar a fronteira modular.

`GET /api/v1/me/next-term` devolve, para cada disciplina ainda não cursada nem em curso: liberada (sem `blocked_by`) ou travada com o motivo explícito — nunca só a etiqueta "travada".

## 2 — `POST /api/v1/me/simulate`: sem Action de escrita, sem transação

```php
// Nenhuma matrícula muda de status de verdade. Remove o código informado do
// conjunto de aprovadas EM MEMÓRIA e recalcula, sobre o MESMO grafo de
// pré-requisitos, a "primeira oportunidade" (longest-path) de cada
// disciplina pendente — comparando cenário real × simulado.
```

A elegibilidade nunca lê o banco direto (recebe o conjunto de aprovadas como argumento) — exatamente o desenho que a Fase 5 do `docs-site` já antecipava: "escrever assim custa zero e transforma a simulação em chamar a mesma função com outro argumento". `SimulateFailureAction` existe só pela validação de entrada (`fail.*` precisa ser um código de disciplina existente) — não há escrita nenhuma para auditar.

**Dois bugs pegos pelos próprios testes, corrigidos antes de fechar:**
1. A disciplina reprovada aparecia na sua própria lista de "afetadas" (trivial — é a entrada da simulação, não um efeito em cascata). Corrigido: excluída explicitamente.
2. `estimated_graduation_delay_terms` (inicialmente um `max()` global sobre toda a matriz) ficava mascarado quando uma disciplina alheia à cadeia (como `EST501`, com `minimum_term` fixo) dominava o máximo nos dois cenários igualmente, escondendo o atraso real da cadeia afetada. Corrigido: agora é o maior atraso **entre as disciplinas efetivamente afetadas**, não um máximo global.

Protegido contra ciclo em pré-requisito (erro de cadastro real no colegiado) com um conjunto de visitados — testado explicitamente.

## 3 — O que existe agora

| Rota | O que faz |
| --- | --- |
| `GET /api/v1/me/next-term` | Liberadas × travadas com o motivo |
| `POST /api/v1/me/simulate` | Simula reprovação — impacto em cascata, sem escrita |

24 endpoints no total agora, 21 tabelas (nenhuma nova nesta entrega).

## Ressalvas honestas

1. **"Período atual" é uma aproximação** (`termsAttended`), não o déficit de CHS completo do documento — ver seção 0. Registrado como pendência do dono do produto em `PROGRESSAO.md`.
2. **`minimum_hours` não é projetado na simulação** — não dá para saber quais disciplinas renderiam quantas horas em qual período futuro sem simular o curso inteiro; fica de fora do `earliestPeriod`, contribuindo só como checagem do estado ATUAL em `next-term`. Não é observado na matriz 45 real, então não afeta a demo.
3. **`estimated_graduation_delay_terms` é uma estimativa pelo grafo de pré-requisitos**, não pelo ritmo de horas do `ProgressReadModel::forecast` — as duas métricas respondem perguntas diferentes ("quando esta disciplina específica libera" × "no ritmo atual, quantos semestres faltam") e não foram unificadas nesta entrega.
4. **Sem tela**, a prova roda por Pest (8 testes dedicados) e por `curl`/Postman.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | `journey` ganha `EligibilityReadModel`; 24 endpoints (Parte X); 192/585 (Parte IX); Parte XII perde a linha de elegibilidade/pré-requisitos |
| `CLAUDE.md` | Mapa de módulos: `journey` — B8 esticada 1 fechada; §Estado atual |
| `docs-site/plano-de-ataque.html` | Item 1 do B8 vira ✅ feito (já feito nesta entrega) |
| `docs-site/features/matricula-e-progressao.html` | Fases 5–6 viram ✅ implementado (já feito nesta entrega) |

Já feito nesta entrega (barato, não esperou o `/sync-docs`): `docs/dominio/PROGRESSAO.md` ganhou as regras 13–14 e a pendência 4 (a investigação do déficit de CHS); os dois arquivos do docs-site acima já atualizados.
