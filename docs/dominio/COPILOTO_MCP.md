# Copiloto MCP — o mesmo Actions, um canal a mais

> **Área:** `app/Mcp` (canal, não módulo) + `tenancy` (token) · **Estado:** ATIVO (MVP) · **Última sincronização:** v0.10.0 · 2026-09-19

## O problema de negócio

O desafio 5.2 pede integração e colaboração — e hoje em 2026 a forma mais
natural de "integrar" um sistema acadêmico é deixar o aluno conversar com ele
dentro do assistente de IA que ele já usa, em vez de abrir mais um app. O
copiloto MCP é a última esticada do B8: sete tools que dão ao Claude (ou
qualquer cliente MCP) o CONTEXTO REAL da graduação do aluno autenticado —
prazo, autoria, semestre e conteúdo saem de tabelas do CampusOS, nunca de
generalidade de internet.

Desenho completo em
[`docs-site/features/copiloto-mcp.html`](../../docs-site/features/copiloto-mcp.html).

## As regras

1. **MCP não é módulo, é canal — igual REST.** As tools vivem em `app/Mcp/`
   (host), não em `app-modules/*/src/Mcp`. É a mesma razão pela qual
   controllers REST vivem dentro de cada módulo mas nunca viram uma segunda
   fonte de regra: aqui a régua é ainda mais estrita, porque nenhuma tool tem
   módulo dono nenhum — todas traduzem argumento de tool para o array que a
   Action ou o read model já esperava.
2. **Se uma tool precisar de um `if` de regra de negócio, pare.** Sinal de que
   a regra não existe na Action e alguém está tentado a inventá-la ali,
   furando a regra de ouro nº 3 pelo canal mais novo do sistema. As sete
   tools deste corte não têm nenhum `if` de negócio — só validação de entrada
   (`ValidationException` quando falta parâmetro) e uma checagem de
   autorização (`AuthorizationException` em `criar_anotacao`).
3. **O escopo de visibilidade é o mesmo do REST.** `acervo_da_disciplina` e
   `buscar_anotacoes` consultam `Note` via Eloquent puro — `NoteVisibilityScope`
   (global scope do model) filtra antes de qualquer tool rodar, exatamente
   como o endpoint HTTP. Nenhum `DB::table()` aqui: ao contrário do painel da
   coordenação (`PAINEL_COORDENACAO.md` regra 3), não existe a armadilha do
   `EntityScope`/`NoteVisibilityScope` não se aplicarem — todas as sete tools
   usam Eloquent do início ao fim.
4. **Token do copiloto é separado do token de login.** `IssueMcpTokenAction`
   (módulo `tenancy`) emite um token com abilities `mcp:read` (sempre) e
   `mcp:write` (opt-in via `allow_write`) através de
   `POST /api/v1/auth/mcp-token`, revogando qualquer token de nome `mcp`
   anterior do mesmo usuário a cada chamada — trocar o token é o próprio
   mecanismo de revogação, não uma tela separada. O token de login comum
   continua com abilities irrestritas (`['*']`) e nunca é aceito na rota MCP.
5. **A rota real exige `mcp:read`; a tool de escrita exige `mcp:write` por
   dentro.** `routes/ai.php` registra `POST /mcp` com
   `['auth:sanctum', 'tenant.user', 'abilities:mcp:read']` — um token sem essa
   ability nunca chega a rodar tool nenhuma. `criar_anotacao` faz uma segunda
   checagem, `auth()->user()->tokenCan('mcp:write')`, dentro do próprio
   `handle()`: defesa em profundidade (a rota já barrou o grosso, a tool
   barra o resto) e a única forma de cobrir essa regra pelo harness rápido de
   teste do pacote (`Server::test()`), que roda em cima de um transporte fake
   e não passa pelo middleware de rota de verdade.
6. **Nenhuma tool toca matrícula, nota (do histórico) ou horas.** Como no
   desenho: a IA nunca grava vínculo, aprovação ou hora complementar — só lê
   o que essas regras já calcularam (`ProgressReadModel`,
   `EligibilityReadModel`, `ComplementaryHoursReadModel`, `AgendaReadModel`)
   ou cria uma anotação pessoal, que nasce sempre `private`
   (`CreateNoteAction` ignora qualquer visibilidade que o argumento da tool
   tente mandar — mesma regra do endpoint REST).

## As sete tools

| Tool | Responde | Escreve? |
| --- | --- | --- |
| `minha_progressao` | Quanto falta, por faixa de hora, com previsão de formatura | não |
| `disciplinas_liberadas` | O que dá para pegar no próximo semestre, e o que trava cada uma | não |
| `acervo_da_disciplina` | As anotações públicas que o aluno tem direito de ver naquela disciplina | não |
| `buscar_anotacoes` | Busca por palavra-chave em todo o acervo a que ele tem acesso | não |
| `minha_agenda` | Tarefas próprias e da turma, por prazo | não |
| `minhas_horas` | Complementares por categoria, com teto e o que foi perdido | não |
| `criar_anotacao` | — | **sim**, a única |

Seis são adaptadores finos de um parágrafo sobre um read model ou Action que
já existia; `criar_anotacao` é a única com uma regra de autorização própria
(regra 5) por ser a única de escrita.

## Decisões e porquês

| Decisão | Alternativas consideradas | Por que esta | Quando revisitar |
| --- | --- | --- | --- |
| `ResolvesRegistration` (trait) vive em `app/Mcp/Tools/Concerns/`, não em `journey` | Mover para um contrato/trait do módulo `journey`, reaproveitável pelos 3 controllers REST que já têm essa mesma busca | O corte era pequeno e mexer nos 3 controllers REST existentes só para extrair uma trait compartilhada não era o objetivo desta esticada — duplicar cinco linhas de lookup é mais barato que arriscar um controller REST já testado | Se um quarto lugar precisar da mesma busca, ou se `journey` ganhar um contrato de leitura de vínculo, mover a trait pra lá e apontar os 3 controllers + as tools pra ele |
| Token do copiloto via Sanctum `abilities`, não um provider OAuth novo | OAuth2 completo (`laravel/passport`), ou um segundo guard de autenticação | Sanctum já é a autenticação de todo o resto da API; abilities resolvem exatamente o requisito do desenho (token trocável, escopo de leitura, escopo de escrita separado) sem nenhuma peça nova de infraestrutura | Se o copiloto precisar de um fluxo de autorização de terceiros (outro serviço pedindo acesso em nome do aluno), OAuth2 vira necessário — não é o caso de um cliente MCP pessoal |
| `criar_anotacao` confere `tokenCan('mcp:write')` dentro do `handle()`, além do middleware de rota | Confiar só no middleware `abilities:mcp:read` da rota, sem checagem na tool | O harness de teste rápido do pacote (`Server::test()`) usa um transporte fake que **não** passa pelo middleware de rota — sem a checagem interna, não haveria como testar essa regra sem cair para o teste HTTP completo em toda tool de escrita futura. Fica como defesa em profundidade também em produção | Se o pacote `laravel/mcp` passar a rodar middleware de rota dentro do próprio harness de teste, a checagem interna continua válida como defesa em profundidade, mas deixa de ser a única forma de testar |

## ⚠️ Pendências do dono do produto

1. **Nenhum teste end-to-end contra um cliente MCP real** (Claude Desktop ou
   equivalente) foi feito — a cobertura é Pest (harness `Server::test()` +
   `postJson('/mcp', ...)` de verdade para a camada de middleware). Nuances
   de negociação de protocolo que só aparecem com um cliente real
   (`ValidateMcpHeaders`, `Accept` headers) não foram verificadas na prática.
2. **Sem UI/tela para o aluno gerar o token do copiloto.** O endpoint
   (`POST /api/v1/auth/mcp-token`) existe e está documentado em `/docs/api`,
   mas não há botão em nenhum front-end — quem for demonstrar precisa chamar
   a rota diretamente (Postman/curl) e colar o token no cliente MCP.

## Mapa de código

| Regra | Onde vive | Teste |
| --- | --- | --- |
| 1 | `app/Mcp/Servers/CampusOsServer.php`, `app/Mcp/Tools/*.php` | `ArchTest` não restringe `app/` de propósito (é raiz de composição) — a fronteira aqui é convenção, não teste de arquitetura |
| 2, 6 | Cada tool em `app/Mcp/Tools/*.php` (corpo do `handle()`) | `tests/Feature/Mcp/CopilotoMcpTest.php` |
| 3 | `AcervoDaDisciplinaTool`, `BuscarAnotacoesTool` (query Eloquent sobre `Note`) | `tests/Feature/Mcp/CopilotoMcpTest.php` |
| 4 | `app-modules/tenancy/src/Actions/IssueMcpTokenAction.php` | `app-modules/tenancy/tests/TokenDoCopilotoTest.php` |
| 5 | `routes/ai.php` (middleware da rota) + `app/Mcp/Tools/CriarAnotacaoTool.php` (checagem interna) | `tests/Feature/Mcp/CopilotoMcpTest.php` — testes via `postJson('/mcp', ...)` real E via `Server::test()` |
