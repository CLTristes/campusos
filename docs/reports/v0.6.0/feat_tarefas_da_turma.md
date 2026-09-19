# 📦 Documento de Entrega — CampusOS · v0.6.0

## Tarefas da turma (B6) — desafio 5.2

> **Data:** 19/09/2026
> **Branch:** `feat/tarefas-da-turma`
> **Escopo:** B6 completo (Fase 3 de `docs-site/features/acervo-compartilhado.html`) — cadastrar já na turma, adotar (cópia), a agenda. Curadoria por voto e `events` (Fase 4, ◎ esticada) ficam para depois.
> **Qualidade:** 174 testes / 526 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

Alguém cadastra um prazo direto na turma, a turma inteira enxerga na própria agenda, e quem decide levar pra própria rotina "adota" — o que cria uma cópia própria, nunca uma linha compartilhada, então marcar como feita nunca conclui para os outros.

## 1 — A tarefa não nasce privada — a criação já é o ato de compartilhar

Diferente da nota (que nasce sempre `private` e precisa de uma Action separada pra publicar), `CreateTaskAction` aceita a `visibility` na própria criação: informar `visibility: offering` + `offering_id` já cadastra a tarefa na turma, num passo só. A mesma guarda de negócio da nota se aplica na criação, não numa Action de publicação: pedir uma visibilidade que exige oferta/disciplina sem a FK correspondente é recusado (422) antes de gravar.

`tsk_visibility` reaproveita o MESMO enum `Lifeos\Enums\Visibility` do acervo de notas — de propósito, pra não repetir a armadilha do LifeOS original (dois enums "quase iguais" em tabelas diferentes).

## 2 — Adotar copia, nunca compartilha a linha

```php
// AdoptTaskAction — a cópia é sempre minha, sempre recomeça.
Task::query()->firstOrCreate(
    ['owner_usr_id' => $userId, 'origin_tsk_id' => $origin->tsk_id],
    ['tsk_status' => TaskStatus::Todo, 'tsk_visibility' => Visibility::Private, ...$origin->only([...])],
);
```

`firstOrCreate` casa com o índice único `(owner_usr_id, origin_tsk_id)` da migration: duplo clique em "adotar" devolve a MESMA cópia, nunca cria uma segunda — testado explicitamente. Adotar uma tarefa `private` de outra pessoa é 403 (adoção não é forma de ler tarefa alheia).

## 3 — `GET /me/agenda`: aqui o filtro por termo EXISTE

Ao contrário do acervo de notas (onde a ausência de filtro por termo É o produto), a agenda filtra por **termo corrente**: pendências próprias (`status != done`) mais as tarefas `offering` das ofertas em que o aluno está matriculado agora, ainda não adotadas — ordenado por prazo. Uma tarefa de oferta de termo já fechado não aparece (testado).

A pergunta "em quais ofertas o aluno está matriculado agora" foi respondida DIRETO em `AgendaReadModel::currentOfferingIdsFor`, via `config('models.registration')`/`config('models.subject_enrollment')` — sem criar um método novo no contrato `EnrolledSubjectsProvider` do `core`. É consulta por FK simples (registration → subject_enrollment → offering → term.status=current), sem a regra de negócio "atravessar termo" que justificou aquele contrato; mesmo raciocínio que `NoteVisibilityScope::offeringIdsFor`/`courseIdsFor` já usam hoje para `course`/`offering`. O valor `'current'` é usado cru (não o enum `CampusOs\Catalog\Enums\TermStatus`) para não importar classe de outro módulo — o `ArchTest` quebraria.

## 4 — O que existe agora

| Rota | O que faz |
| --- | --- |
| `POST /api/v1/tasks` | Cria — aceita `visibility` na hora (não força `private`) |
| `GET /api/v1/me/agenda` | Pendências próprias + tarefas `offering` do termo corrente, ainda não adotadas |
| `POST /api/v1/tasks/{task}/adopt` | Adota — cria cópia privada, idempotente |
| `PATCH /api/v1/tasks/{task}/status` | Muda o status — só o dono |

20 endpoints no total agora, 19 tabelas.

## 5 — `Task` não ganhou um global scope de visibilidade

Ao contrário de `Note`+`NoteVisibilityScope`, `Task` não tem um scope genérico lendo os 5 níveis em toda query. Nenhum endpoint do B6 lista tarefa de terceiro fora da agenda — que já filtra explicitamente (`offering` + oferta do termo atual + `whereDoesntHave('adoptions', ...)`). Construir o scope completo (course/subject/institution) cobriria uma capacidade que nada usa hoje — decisão registrada em `TAREFAS.md` para revisitar se surgir um `GET /tasks?subject_id=` nos moldes do acervo de notas.

## Ressalvas honestas

1. **Curadoria por voto e `events` (Fase 4, ◎ esticada) não entraram** — fora de escopo desta entrega, exatamente como o desenho já marcava.
2. **Sem edição de conteúdo.** Só existe criar, adotar e mudar status — título/descrição/prazo não têm endpoint de update depois de criada. Não apareceu como pedido explícito do desenho (que só fala em "marcar como feita"); se o front precisar, é uma Action pequena a mais (autor apenas), no mesmo padrão de `UpdateTaskStatusAction`.
3. **Sem notificação de mudança na origem.** "Se o professor adia a entrega, quem adotou pode ser avisado" é uma frase do desenho — o vínculo `origin_tsk_id` já existe pra isso, mas propagação/notificação não têm código.
4. **`project_prj_id` existe na tabela sem FK** — `projects` é ◎ esticada e ainda não tem tabela; a coluna existe hoje pra não exigir migração depois (mesma decisão já registrada no `banco-de-dados.html` para esta coluna).
5. **Sem tela**, a prova roda por Pest (10 testes dedicados) e por `curl`/Postman.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | `lifeos` ganha `tasks` (Parte V); Parte X ganha 4 endpoints (20 no total); tabela sobe de 18 para 19 |
| `CLAUDE.md` | Mapa de módulos: `lifeos` — B6 fechado, só curadoria/`events` faltam; §Estado atual |
| `docs/arquitetura/BANCO.md` | Ownership de `tasks` |
| `docs-site/features/acervo-compartilhado.html` | Fase 3 vira ✅ implementado |
| `docs-site/plano-de-ataque.html` | B6 vira ✅ feito |
| `docs-site/banco-de-dados.html` | Card `tasks` vira ✅ em código |

Já feito nesta entrega (barato, não esperou o `/sync-docs`): `docs/dominio/TAREFAS.md` criado; `docs/README.md` e a pendência nº 2 de `ACERVO.md` atualizados para apontar pra ele.
