# 📦 Documento de Entrega — CampusOS · v0.2.0 (iteração 2)

## Jornada do aluno e progressão da graduação

> **Data:** 19/09/2026
> **Branch:** `feat/journey-progressao`
> **Escopo:** bloco B3 do plano de ataque — o coração do desafio 5.1. Pedido pelo dono do produto com a regra de aprovação fornecida na abertura da iteração.
> **Qualidade:** 103 testes / 330 asserts verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O sistema passa a responder *"quanto falta para eu me formar?"* — com as três
faixas de carga horária, o que ainda está pendente e a previsão de formatura
calculada pelo ritmo real do aluno.

## 1 — A regra de aprovação como domínio (`ApprovalPolicy`)

`app-modules/journey/src/Support/ApprovalPolicy.php`

A regra informada pelo dono do produto, virada código:

| Frequência | Exigência de nota |
| --- | --- |
| < 50 % | nenhuma — reprovado independente da nota |
| 50 % ≤ f < 75 % | média ≥ **8,0** |
| ≥ 75 % | média ≥ **6,0** |

**Decisão central: esta classe NÃO decide a situação de uma matrícula
importada.** O sistema grava o que o documento oficial imprime, sempre. A
política serve para (1) o alerta preventivo — *"você está com 62 %, daqui em
diante precisa de 8,0"* —, (2) a simulação de reprovação e (3) conferir a
importação.

Por que assim: recalcular a situação significaria o sistema discordar do
histórico oficial do aluno. Divergência entre o previsto e o impresso é sinal
de leitura errada do documento, não de regra errada — e é exatamente o que a
divergência do `ISI604` (ressalva 1) demonstra.

**Dois achados durante a verificação contra o histórico real:**

- **Zero de frequência não é falta.** `EST501` (Estágio) aparece com `0,0` e
  situação *Aprovado*: estágio não tem aula. `sen_attendance` é **nullable** e
  `null` significa "não se aplica" — tratar esse zero como falta reprovaria todo
  mundo que fez estágio.
- **O rótulo da situação codifica a faixa de frequência.** Nos 14 aprovados do
  histórico, sem exceção: `Aprovado Por Nota/Frequência` ⟺ f ≥ 75 %, e
  `Aprovado Por Nota` ⟺ f < 75 %. `ApprovalPolicy::attendanceRangeFromLabel()`
  usa isso para inferir a faixa quando o campo vem vazio (`*`) no documento.

## 2 — As três tabelas do `journey`

`students` · `registrations` · `subject_enrollments`

- **`students` é separada de `users`** pelo mesmo motivo que `patients` é no
  FibroMais: identidade acadêmica ≠ identidade de login. `user_usr_id` nullable
  permite importar uma turma antes de as pessoas criarem conta.
- **`registrations.curriculum_cur_id` é uma FOTO** da matriz vigente no
  ingresso e nunca se atualiza. Há teste: publicar matriz nova não move o
  vínculo de quem já ingressou, mas quem ingressa depois pega a nova.
- **O índice único de `subject_enrollments` é (vínculo, disciplina, TERMO).**
  Cursar de novo em outro semestre gera linha nova — é assim que reprovação
  aparece no histórico, e daí sai a contagem de tentativas de graça. Há teste
  para os dois lados: a segunda linha em outro termo passa; no mesmo termo o
  banco barra.

## 3 — `ProgressReadModel` — o cálculo

`app-modules/journey/src/ReadModels/ProgressReadModel.php`

Calculado, nunca armazenado: um percentual em coluna fica velho no instante em
que uma nota muda. Três queries somadas em memória.

**Três faixas, não cinco nem duas** — obrigatórias (2.730 h) + optativas (210 h)
+ extensão autônoma (60 h) = **3.000 h**, pela fórmula do documento da matriz.
A carga extensionista total (300 h) é um indicador **ortogonal**, com
`counts_in_total: false` explícito no JSON.

**Cada faixa satura no próprio teto** (`ProgressTrack::counted()`), e o
excedente aparece como `surplus` sem inflar o total — senão um aluno com 400 h
extras de optativa apareceria "formado" com o estágio zerado. Há teste.

**A previsão usa o ritmo real** (horas aprovadas ÷ semestres cursados), não o
ideal da matriz. O ideal engana; o real é o que a pessoa precisa ouvir.

## 4 — `GET /api/v1/me/progress`

O endpoint que sozinho desenha a tela principal. Devolve `registration`,
`overall`, `tracks[]`, `extension`, `pending_subjects[]` e `forecast`.

Aluno sem vínculo recebe **404 com instrução do que fazer** ("Envie seu
histórico escolar"), não um erro genérico.

## Ressalvas honestas

1. **`ISI604` contradiz a regra de aprovação.** No histórico real: média **7,4**
   com frequência **73,0 %** — faixa que exigiria 8,0 — e o documento diz
   *Aprovado Por Nota*. Hipóteses não verificadas: abono de falta, arredondamento
   do cálculo oficial, conselho de classe, ou o corte dessa faixa não ser 8,0.
   **Impacto:** nenhum na importação; afeta a precisão do alerta preventivo e da
   simulação. **Registrado como pendência 1 de `PROGRESSAO.md`.**
2. **A faixa `standalone_extension` está sempre em 0 cumprido.** As 60 h de
   Componente Curricular Extensionista autônomo precisam de uma tabela própria
   (o CCE não é disciplina), que não existe ainda. A faixa aparece na barra com
   o valor exigido correto, mas ninguém consegue cumpri-la pelo sistema.
3. **`ProgressReadModel` não aplica pré-requisito.** `pending_subjects` lista o
   que falta, sem dizer o que está **travado** — as Fases 5 e 6 do card
   (elegibilidade e simulação) são a próxima entrega, e a `EligibilityReadModel`
   ainda não existe.
4. **Atividades complementares não entram na conta.** Dependem de
   `complementary_categories`, que depende da resolução do curso (pendência 2
   de `PROGRESSAO.md`).
5. **`CreateRegistrationAction` não tem endpoint HTTP.** É chamada por teste e
   será chamada pela confirmação da importação de documento (B4). Expor um
   `POST /registrations` antes disso seria criar superfície sem caso de uso.
6. **O período do aluno (regra 10 de `PROGRESSAO.md`) não é calculado.** O
   déficit de CHS está modelado (`cur_max_weekly_deficit`, `sbj_weekly_hours`)
   mas o `forecast` ainda não o usa — hoje devolve `terms_attended`, não o
   período oficial.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/dominio/PROGRESSAO.md` | **Criado nesta entrega** com as 12 regras e o mapa de código. Já está atualizado. |
| `docs/reports/SISTEMA.md` | Módulo `journey` com 3 tabelas; o endpoint `/me/progress`; a `ApprovalPolicy` como regra central. |
| `CLAUDE.md` | §Estado atual (103 testes), mapa de módulos (`journey` deixa de ser esqueleto), §Fluxo canônico. |
| `docs/arquitetura/BANCO.md` | Ownership de `students`, `registrations`, `subject_enrollments` + ER. |
| `docs/dominio/DOCUMENTOS_ACADEMICOS.md` | §1.3: apontar que o vocabulário virou o enum `EnrollmentStatus`, e que o rótulo revela a faixa de frequência. |
| `docs-site/features/matricula-e-progressao.html` | Fases 1, 3 e 4 viram ✅ implementado; a regra de aprovação entra na Fase 3. |
| `docs-site/banco-de-dados.html` | 3 cards para ✅ em código. |
