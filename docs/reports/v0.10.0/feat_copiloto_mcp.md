# 📦 Documento de Entrega — CampusOS · v0.10.0

## Copiloto MCP (B8, terceira esticada) — desafio 5.2

> **Data:** 19/09/2026
> **Branch:** `feat/copiloto-mcp`
> **Escopo:** Fases 1–2 de `docs-site/features/copiloto-mcp.html` — as sete tools completas e a autenticação do canal (token separado, com escopo de escrita opt-in). Fora do escopo: teste end-to-end contra um cliente MCP real (ver Ressalvas).
> **Qualidade:** 217 testes / 679 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O aluno conecta o CampusOS ao Claude (ou a qualquer cliente MCP) e conversa sobre a própria graduação com contexto real — progressão, disciplinas liberadas, agenda, acervo do veterano, horas complementares e criação de anotação — pelas mesmas Actions e read models que já respondem ao REST, nunca uma segunda fonte de regra.

## 0 — Achado fora do escopo, corrigido antes de seguir: `tasks` não migrava em Postgres real

Antes de fechar esta esticada, rodei `php artisan migrate` contra o Postgres de dev (as migrations de B6/B7/B8-esticada-3 estavam pendentes ali desde antes de B6). A migration de `tasks` (B6, já mergeada) falhava: `origin_tsk_id` referencia `tasks.tsk_id` dentro do mesmo `Schema::create`, e a grammar do Postgres sempre emite `alter table ... add primary key` como a **última** statement desse bloco — a FK auto-referenciada tentava se ligar antes da PK existir. O sqlite (banco de teste) não reproduz essa ordem, por isso passou despercebido por três esticadas. Corrigido movendo a FK auto-referenciada para um `Schema::table()` separado, depois do `create` — sem mudar nenhuma coluna, índice ou o `down()`. Reconferido: as quatro migrations pendentes (B6, B7 × 2, esta esticada) aplicam limpo, e os 217 testes continuam verdes.

## 1 — MCP é canal, não módulo

Sete tools em `app/Mcp/Tools/` (host, não `app-modules/`) — a mesma régua do REST: nenhuma lógica de negócio nova, só tradução de argumento de tool para o array que a Action ou o read model já esperava. `CampusOsServer` (`app/Mcp/Servers/`) declara as sete; `routes/ai.php` registra `POST /mcp` com `auth:sanctum` + `tenant.user` + `abilities:mcp:read`.

## 2 — Seis de leitura, adaptadores de um parágrafo

`minha_progressao`, `disciplinas_liberadas`, `minha_agenda` e `minhas_horas` chamam direto o read model existente (`ProgressReadModel`, `EligibilityReadModel`, `AgendaReadModel`, `ComplementaryHoursReadModel`). `acervo_da_disciplina` e `buscar_anotacoes` consultam `Note` via Eloquent puro — `NoteVisibilityScope` filtra antes de qualquer tool rodar, sem exceção nem `DB::table()` em lugar nenhum: ao contrário do painel da coordenação, aqui não existe a armadilha do global scope não se aplicar.

## 3 — A tool de escrita, e a única checagem de negócio de fato

`criar_anotacao` é a única de escrita — chama `CreateNoteAction`, que já ignora qualquer visibilidade que o argumento tente mandar (a anotação nasce sempre `private`, igual ao REST). A única regra que a tool decide por conta própria é de autorização, não de negócio: confere `tokenCan('mcp:write')` dentro do `handle()`, além do `abilities:mcp:read` que a rota já exige de todas — defesa em profundidade, e a única forma de cobrir essa regra pelo harness rápido de teste do pacote (`Server::test()`), que roda sobre um transporte fake e não passa pelo middleware de rota de verdade.

## 4 — Token do copiloto, separado do token de login

`IssueMcpTokenAction` (módulo `tenancy`) emite, via `POST /api/v1/auth/mcp-token`, um token Sanctum com ability `mcp:read` sempre e `mcp:write` só se `allow_write: true` — revogando qualquer token de nome `mcp` anterior do mesmo usuário a cada chamada, então gerar de novo já é o mecanismo de revogação. O token de login comum continua com abilities irrestritas e nunca é aceito na rota `/mcp`.

## 5 — O que existe agora

| Tool | Escreve? |
| --- | --- |
| `minha_progressao` | não |
| `disciplinas_liberadas` | não |
| `acervo_da_disciplina` | não |
| `buscar_anotacoes` | não |
| `minha_agenda` | não |
| `minhas_horas` | não |
| `criar_anotacao` | **sim**, a única |

Mais o endpoint `POST /api/v1/auth/mcp-token`. 29 endpoints documentados em `/docs/api` agora (28 + este), 21 tabelas (nenhuma nova — o copiloto não introduz schema próprio).

## Ressalvas honestas

1. **Nenhum teste end-to-end contra um cliente MCP real** (Claude Desktop ou equivalente) foi feito. A cobertura é Pest: o harness `Server::test()` do pacote (fake transport, prova a lógica de cada tool) mais `postJson('/mcp', ...)` real (prova o middleware de rota — sem token, token sem `mcp:read`, etc). Nuances de negociação de protocolo que só aparecem com um cliente de verdade não foram verificadas na prática.
2. **Sem UI para o aluno gerar o próprio token.** O endpoint existe e está documentado, mas quem for demonstrar precisa chamar a rota diretamente e colar o token no cliente MCP à mão.
3. **`ResolvesRegistration` (a busca de vínculo mais recente) vive numa trait nova em `app/Mcp/Tools/Concerns/`**, não reaproveitando os 3 controllers REST que já fazem a mesma busca — decisão deliberada para não mexer em código já testado por uma esticada de tempo apertado; registrada em `docs/dominio/COPILOTO_MCP.md` para revisitar se aparecer um quarto lugar.
4. **A migration de `tasks` (item 0) é uma correção a código já mergeado e reportado em v0.6.0** — não reabre aquele report (reports são fotografias datadas), mas o schema real só ficou íntegro em Postgres a partir desta entrega.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | Novo canal MCP (`app/Mcp/`); 29 endpoints (Parte X); 217/679 (Parte IX); Parte XII perde a linha do copiloto MCP — B8 fecha as três esticadas |
| `CLAUDE.md` | §Estado atual ganha o copiloto MCP; "Ainda não existe" perde a menção ao B8 esticada 3 |

Já feito nesta entrega (barato, não esperou o `/sync-docs`): `docs/dominio/COPILOTO_MCP.md` criado; `docs-site/features/copiloto-mcp.html` e `docs-site/plano-de-ataque.html` já refletem ✅ em código.
