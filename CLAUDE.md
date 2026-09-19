# CLAUDE.md — Template Laravel Modular

Guia de padrões para qualquer pessoa (ou agente) que trabalhe neste repositório.
**Leia antes de escrever código.** Este arquivo é o contrato: a IA opera DENTRO
destas regras, e as regras são executáveis (Pest + Pint + ArchTest quebram se
forem violadas).

## Visão

O **CampusOS** é a gestão da graduação e o acervo acadêmico compartilhado entre
veteranos e calouros. Nasceu no **HackLab UTFPR 2026** para o desafio **5.1**
(jornada acadêmica e planejamento da graduação), cobrindo o **5.2** (integração
e colaboração na comunidade acadêmica) com a mesma base de dados.

**A matrícula é a chave dos dois.** Quando o sistema sabe que o aluno está em
*Cálculo 2, turma S71, 2026/1*, a barra de progresso dele anda (5.1) **e** ele
passa a enxergar tudo que qualquer veterano deixou público naquela disciplina,
em qualquer semestre (5.2). É o mesmo dado sustentando os dois desafios — por
isso é um produto só.

Quatro capacidades:

1. **Catálogo acadêmico** — curso, matriz, disciplina, pré-requisito, oferta;
   importados de documento, nunca escritos em código.
2. **Jornada do aluno** — vínculo, histórico, progressão da graduação, horas
   complementares, certificados.
3. **Acervo compartilhado** — a anotação pertence à *disciplina*, não à turma,
   então sobrevive ao semestre que a escreveu.
4. **Indicadores de coordenação** — agregados anônimos, nunca aluno nomeado.

**Multi-tenant pela `Entity` = a instituição de ensino.** Curso, matriz e
disciplina são dados, não código — é o que faz o produto funcionar em qualquer
universidade no dia seguinte. Piloto: UTFPR, Câmpus Francisco Beltrão, curso 25
(Sistemas de Informação), matriz 45.

**Nenhuma integração com sistema acadêmico.** Todo dado entra por documento que
a própria universidade já emite — ver
[`docs/dominio/DOCUMENTOS_ACADEMICOS.md`](docs/dominio/DOCUMENTOS_ACADEMICOS.md).

## A verdade mora na documentação

A documentação canônica vive em [`docs/`](docs/README.md) (se o projeto tiver uma
base externa — Notion, wiki — ela vence o repositório; registre isso aqui). **Se
este arquivo ou o código divergirem da documentação canônica, a documentação
vence.** Em caso de ambiguidade genuína ou lacuna, **pare e pergunte** — não
assuma. Decisões de produto pertencem ao dono do produto, não à IA.

### ⚑ Leia `docs-site/` ANTES de qualquer coisa

Este projeto é o **CampusOS** — HackLab UTFPR 2026, desafio 5.1 (jornada
acadêmica) como principal e 5.2 (integração calouro↔veterano) como cobertura.
O desenho completo foi escrito **antes da primeira linha de código** e vive em
[`docs-site/index.html`](docs-site/index.html) (site estático, fora do Laravel):

| Página | O que tem |
| --- | --- |
| `docs-site/plano-de-ataque.html` | **Ordem de implementação com hora marcada**, pontos de corte e roteiro de demo |
| `docs-site/banco-de-dados.html` | As 24 tabelas, 6 módulos, com a razão de cada decisão de modelagem |
| `docs-site/features/*.html` | O desenho de cada feature, em duas leituras (regra de negócio × código) |
| `docs-site/identidade-visual.html` | Paleta e tokens — não escolha cor, use os de lá |

**Todo card lá é canônico**, no mesmo espírito do FibroMais: `⚑ MVP` e
`◎ esticada` são decisão de produto **já tomada**, só ainda sem código — trate
com a mesma autoridade de uma regra em `docs/dominio/`, nunca como rascunho.
Divergiu ao implementar? Mude o código **e vire o card junto**.

O índice traz uma seção **"⚠️ Pendências do dono do produto"** com as perguntas
que ninguém respondeu ainda. Elas não foram resolvidas por conta própria (regra
de ouro nº 1) — se o seu trabalho depender de uma delas, **pergunte ao Felipe**.

> O `/prontuario` ainda **não rodou**: a Visão abaixo segue sendo a do template
> genérico e os módulos de exemplo continuam no lugar. O `docs-site` já contém
> quase todas as respostas da entrevista — use-o como entrada em vez de
> perguntar tudo de novo.

## Stack definitiva

- **PHP 8.2+** (tipagem estrita em tudo)
- **Laravel 12**
- **PostgreSQL 16 próprio** em produção — sem Postgres-as-a-service (dev
  rápido: sqlite; `compose.yaml` sobe pg16+redis7)
- **Eloquent** como **única** camada de acesso a dados
- **Redis 7** (filas + cache) via `predis` — produção; filas **nomeadas** por
  prioridade de negócio
- **Laravel Horizon** — workers/filas (`php artisan horizon` em Linux/produção;
  no Windows dev use `queue:work`/`queue:listen` — `ext-pcntl` é Unix-only; o
  `composer.json` tem platform-fakes de `ext-pcntl`/`ext-posix` só para instalar)
- **internachi/modular** — arquitetura modular (módulos em `app-modules/`)
- **Pest** — testes (DB de teste = `sqlite :memory:`, fila `sync`)
- **Pint** — estilo (preset `laravel` + `declare_strict_types`)
- **Front-end**: qualquer SPA (React, Angular, Vue) consumindo a API — o backend
  não renderiza telas; opcionais documentados no README §Dependências (Filament,
  Inertia, Pulse, laravel/mcp...)

## Paradigma: arquitetura MODULAR (não por camadas)

O projeto é um **modular monolith** com `internachi/modular`. O domínio vive em
`app-modules/<módulo>/` — **não** em `app/Services`, `app/Controllers`, etc. Cada
módulo é um pacote Composer com namespace `Modules\<Modulo>\` e fronteira própria:
um módulo **nunca** importa a classe interna de outro; eles falam por **contratos**
e **eventos** declarados no módulo `core`. Detalhe completo em
[`docs/arquitetura/ARQUITETURA.md`](docs/arquitetura/ARQUITETURA.md).

**Mapa de módulos:**

| Módulo | Estado | Responsabilidade |
| --- | --- | --- |
| `core` | do template | shared kernel: `AbstractAction`, `Entityable`, `EntityScope`, `TenantContext`, `AuditObserver` |
| `tenancy` | **em código** | `Entity` (a instituição) + `Campus` + `User` (4 papéis) + login/cadastro Sanctum |
| `catalog` | **em código** | dados mestres: curso, matriz, disciplina, pré-requisito, equivalência, conjunto de optativas, semestre, oferta |
| `journey` | **parcial** | vínculo, histórico, `ApprovalPolicy`, `ProgressReadModel`, importação de documento (sobe→confere→confirma), horas complementares (B7 — nunca soma na progressão). Falta: elegibilidade, endpoint de criar vínculo |
| `lifeos` | **parcial** | o acervo do veterano (B5): `Note` + escada de visibilidade de 5 níveis. B6 (tarefas da turma): `Task` + adoção por cópia + `GET /me/agenda`. Falta curadoria por voto e `events` (esticada) |
| `insights` | esqueleto | agregados da coordenação. **Sem tabelas por desenho** — lê por read model |
| `integrations` | **em código** | `GeminiDocumentExtractor` + `NullDocumentExtractor` atrás do contrato `AcademicDocumentExtractor`. **O único lugar que sabe qual IA lê os documentos** |

## As 10 regras de ouro

1. **A verdade mora na documentação.** `docs/` é canônico; em dúvida, pergunte —
   nunca assuma decisão de produto.
2. **Eloquent é a única camada de dados.** Sem SQL cru / query builder para regra
   de negócio; sem Postgres gerenciado de terceiros.
3. **Fronteira de módulo é sagrada.** Toda lógica vive em **Actions** dentro do
   módulo dono; os canais (REST, MCP, CLI) são adaptadores **finos** sobre as
   mesmas Actions. Comunicação entre módulos só por **contrato** (síncrono),
   **evento** (assíncrono) ou **read model/DTO** (leitura) — nunca importando o
   interno de outro módulo. O teste de arquitetura quebra o CI se a fronteira
   for furada.
4. **Multi-tenancy sempre.** Todo model com escopo de tenant usa `Entityable` +
   `EntityScope` (ambos no `core`); nunca cruze tenants; restaure o contexto com
   `TenantContext::runAs()` em jobs, workers e em qualquer canal sem sessão HTTP.
5. **`declare(strict_types=1)` em todo arquivo PHP.** Tipagem estrita, garantida
   pelo Pint e verificada pelo ArchTest.
6. **Segredos nunca em claro.** Credenciais/segredos cifrados ou como hash;
   nenhuma credencial real no repositório; `.env.example` só com placeholders.
7. **Trabalho pesado é assíncrono e resiliente.** `POST` de operação demorada
   retorna **202** + Job em fila nomeada; jobs reprocessam em timeout/5xx
   (**nunca os capturam** — erro de transporte lança, recusa de negócio vira
   estado final); idempotência pela `ref` do cliente.
8. **Tudo auditável e imutável.** Mutações gravadas via **Observers** (que
   estendem o `AuditObserver` do core) em `audit_logs` (append-only); colunas
   sensíveis fora do diff via `$hidden` do observer.
9. **Padrões sempre verdes.** Pest + Pint + ArchTest barram cada mudança;
   convenções de nomenclatura (prefixo de 3 letras, UUID PK, SoftDeletes,
   FKs `{tabela_singular}_{pk}`) sem exceção.
10. **Nenhuma entrega termina sem relatório.** Toda feature fecha com um
    `docs/reports/vX.Y.Z/feat_*.md` e uma entrada `pending` no
    `docs/reports/sync-state.json`; a skill `/sync-docs` propaga os reports
    pendentes para a documentação evergreen (`SISTEMA.md`, `docs/dominio/`,
    este arquivo). Reports são fotografias datadas — não se atualizam
    retroativamente.

## Mapa de pastas

```
app/                          # app host ENXUTA: borda e ferramenta interna. SÓ ISSO.
  Http/Middleware/            #   ResolveTenantFromUser (alias tenant.user — a borda
                              #     REAL: tenant vindo do usuário autenticado) +
                              #     ResolveTenantFromHeader (placeholder do template)
  Providers/Filament/         #   DataConsolePanelProvider — o painel /data-console
  Filament/                   #   Auth/Login (a coluna é usr_email, não email) +
                              #     Resources/ do console (ferramenta de dev/QA,
                              #     não domínio — por isso vivem aqui, não nos módulos)
app-modules/                  # <- todo o domínio vive aqui
  core/                       # shared kernel: AbstractAction, Entityable, EntityScope,
                              #   TenantContext, AuditLog, AuditObserver, audit:query
  tenancy/                    # Entity (instituição) + Campus + User (4 papéis) +
                              #   LoginAction/Sanctum (/api/v1/auth)
  catalog/                    # curso, matriz, disciplina, curriculum_subjects,
                              #   pré-requisito, equivalência, conjunto de optativas,
                              #   semestre, oferta, complementary_categories (B7)
                              #   + o seeder da matriz real
    database/data/            #   CSVs da matriz + parse_matriz_html.py (o gerador)
  journey/                    # students, registrations, subject_enrollments +
                              #   ApprovalPolicy + ProgressReadModel (/api/v1/me/progress)
                              #   + complementary_activities (B7, nunca soma na progressão)
  lifeos/                     # parcial — Note (B5) + Task/agenda (B6);
                              #   falta curadoria por voto e events (esticada)
  insights/                   # esqueleto — agregados da coordenação, SEM tabelas
  integrations/               # GeminiDocumentExtractor + NullDocumentExtractor
                              #   atrás do contrato do core. O bind no provider é
                              #   o ÚNICO lugar que escolhe o provedor de IA
config/app-modules.php        # config do internachi/modular (namespace CampusOs\)
config/models.php             # bindings de model cross-módulo (relações por config)
config/scribe.php             # documentação da API (UI Scalar em /docs/api)
docs/                         # índice em docs/README.md
  arquitetura/                #   COMO: ARQUITETURA, MODULOS, CORE, COMUNICACAO,
                              #     FRONTEIRAS, IMPLEMENTACAO, BANCO, CODE_STYLE
  dominio/                    #   O QUE: DOCUMENTOS_ACADEMICOS.md (a estrutura dos
                              #     três documentos do portal) + PROGRESSAO.md (as
                              #     12 regras da barra de progresso)
  reports/                    #   entregas versionadas + SISTEMA.md (evergreen)
                              #     + sync-state.json (versionamento de contexto)
docs_referencia/              # os documentos REAIS do portal (histórico, matriz,
                              #   requerimento) — fonte do extrator e dos seeds
docs-site/                    # site estático: o desenho de produto, anterior ao
                              #   código e canônico para o que ainda não existe
tests/                        # Unit/ Feature/ Arch/ da raiz; módulos têm tests/
.claude/skills/               # prontuario, dominio, nova-feature, sync-docs,
                              #   novo-modulo
```

Cada módulo é um mini-Laravel (`src/`, `routes/`, `database/`, `tests/`). O que num
app normal fica em `app/Models`, `app/Jobs`, `app/Http`, aqui fica em
`app-modules/<modulo>/src/...`. Árvores completas em
[`docs/arquitetura/MODULOS.md`](docs/arquitetura/MODULOS.md).

## Fluxos canônicos

**1. Implantação (coordenação).** O documento da matriz vira catálogo. Hoje via
`MatrizUtfprSeeder`, que lê os CSVs gerados por
`app-modules/catalog/database/data/parse_matriz_html.py` a partir do HTML da
tela do portal. A leitura por IA é a próxima entrega.

**2. Login.** `POST /api/v1/auth/login` → `LoginAction` → token Sanctum. O
e-mail é único **por instituição**, então a Action roda fora do escopo de tenant
(é do usuário encontrado que o tenant sai) e, em ambiguidade, pede `entity_id`
em vez de escolher. Rotas autenticadas passam por `auth:sanctum` + `tenant.user`.

**3. Progressão.** `GET /api/v1/me/progress` → `ProgressReadModel`. Três faixas
(obrigatórias 2730 + optativas 210 + extensão autônoma 60 = **3000 h**), cada
uma saturando no próprio teto; a carga extensionista total é indicador
**ortogonal** e não entra no somatório. Previsão pelo ritmo **real** do aluno.

**4. Importação do documento do aluno.** `POST /api/v1/me/academic-documents`
→ **202** + `ParseAcademicDocumentJob` (fila `imports`) → o extrator lê →
`parsed` → o aluno revisa → `POST .../confirm` cria as matrículas.

**A IA nunca escreve matrícula.** E a fronteira transporte × negócio é rígida:
5xx, timeout, **429 de cota** e credencial ausente **sobem** e a fila reprocessa;
"não é documento acadêmico" vira estado final. Trocar de provedor é mudar
`DOCUMENT_EXTRACTOR` — o `journey` não conhece o Google.

## Comandos úteis

```bash
composer dev            # serve + queue:listen + pail + vite (concurrently)
composer test           # pest (Unit + Feature + Modules + Arch)
composer test:coverage  # pest --coverage --min=70
composer lint           # pint (corrige)
composer lint:check     # pint --test (CI / antes de commitar)
php artisan about       # status do ambiente (deve terminar sem erros)
php artisan modules:list
php artisan make:model X --module=<modulo>   # make:* com --module
php artisan audit:query <tabela> <id>        # trilha de auditoria de um registro
php artisan migrate
composer docs            # regenera a documentação da API (Scribe → /docs/api)
php artisan horizon     # workers (Linux/produção; Windows: queue:work)
docker compose up -d    # PostgreSQL 16 + Redis 7 locais (ou use um Postgres local)
```

## Ciclo de feature (o "batimento")

Toda feature segue o mesmo ritual — a skill **`/nova-feature`** o executa:

```
tarefa definida → branch feat/... → implementar por blocos (teste junto)
  → Pest + Pint + ArchTest verdes → relatório em docs/reports/vX.Y.Z/feat_*.md
  → entrada "pending" no sync-state.json → merge --ff-only + push
  → (quando conveniente) /sync-docs propaga os reports pendentes p/ docs evergreen
```

Honestidade é invariante: **nunca** declare uma entrega completa se não estiver
100% (testes verdes, sem ressalvas ocultas); ressalvas vão EXPLÍCITAS no
relatório. Commits em português, imperativos, prefixados (`feat:`, `fix:`,
`chore:`, `docs:`, `refactor:`, `test:`); rode `composer lint:check && composer
test` antes de commitar.

## Skills disponíveis (.claude/skills/)

| Skill | Quando usar |
| --- | --- |
| `/prontuario` | **Primeira coisa num sistema novo**: entrevista o dono do produto, gera docs/dominio, renomeia projeto/namespace, remove exemplos |
| `/dominio` | Antes de responder/planejar qualquer coisa de negócio: carrega docs/dominio + SISTEMA.md para a sessão |
| `/nova-feature` | Executar o ciclo de feature completo (branch → código+testes → report → sync-state) |
| `/sync-docs` | Propagar reports pendentes (sync-state.json) para a documentação evergreen |
| `/novo-modulo` | Criar um módulo novo com TODAS as convenções (composer, provider, ArchTest, docs) |

## Estado atual

**v0.7.0 (19/09/2026) — 184 testes / 557 asserções verdes**, Pint verde,
ArchTest verde. 21 tabelas em código, 22 endpoints documentados em `/docs/api`.

O **catálogo acadêmico** está completo, com a matriz 45 da UTFPR real semeada:
121 disciplinas, 34 pré-requisitos, 98 equivalências. O total a integralizar
(3.000 h) é conferido por teste contra a fórmula que o próprio documento imprime.

A **jornada do aluno** responde `GET /api/v1/me/progress` — a barra de progresso
existe. `ApprovalPolicy` traz a regra de aprovação da UTFPR (frequência < 50 %
reprova; 50–75 % exige média 8,0; ≥ 75 % exige 6,0), verificada contra sete
casos do histórico real. **O sistema não recalcula a situação de uma matrícula
importada** — grava o que o documento imprime (decisão do dono do produto).

A **importação de documento** funciona ponta a ponta: sobe → a IA lê em fila →
o aluno confere → confirma → as matrículas nascem e a barra anda. O provedor é o
Gemini, atrás de contrato; `DOCUMENT_EXTRACTOR=null` desliga a leitura e manda
tudo para a tela de conferência (é o extrator dos testes e o plano B da demo).
**Validado contra a API real** (`gemini-3.6-flash`) com o histórico e a matriz
reais do dono do produto — leitura das 57 linhas do histórico batendo 100% com
o documento, `"*"` de frequência sempre virando `null`.

`registration_id` na confirmação é **opcional**: sem ele, o vínculo (Student +
Registration) nasce sozinho do `meta` que a IA leu do cabeçalho do documento
(curso, RA, semestre de ingresso) — não existe mais o buraco de "aluno sem
vínculo não consegue importar nada".

**Acesso:** login por token, 4 papéis, console de dados Filament em
`/data-console` (só coordenação e gestão) e documentação de API em `/docs/api`.
**Cadastro livre do aluno** existe: a instituição é resolvida pelo domínio do
e-mail (`entities.ent_email_domain`), acesso é imediato (token sai do próprio
cadastro), e um código de verificação por e-mail confirma o endereço como
camada de segurança em paralelo, sem bloquear nada.

O **acervo do veterano** (`lifeos`, desafio 5.2) tem B5 e B6 em código: `Note`
publica numa escada de 5 visibilidades (`private→offering→subject→course→
institution`) que atravessa semestres de propósito (`NoteVisibilityScope`);
`Task` reaproveita o MESMO enum — ao contrário da nota, uma tarefa pode nascer
já compartilhada (`visibility: offering` na criação) — e `AdoptTaskAction`
copia a tarefa da turma pro aluno (nunca a linha original), com
`GET /api/v1/me/agenda` juntando pendências próprias e o que a turma
compartilhou nas ofertas do termo corrente. Ver
[`ACERVO.md`](docs/dominio/ACERVO.md) e [`TAREFAS.md`](docs/dominio/TAREFAS.md).

**Horas complementares** (`journey`+`catalog`, desafio 5.1, B7) também está em
código: `complementary_categories` guarda o teto por categoria (dado da
matriz), o aluno declara em `POST /api/v1/me/complementary-activities` com
certificado opcional, e o corte pelo teto é calculado **agregado por
categoria** (nunca por certificado) em `ComplementaryHoursReadModel`. Decisão
do dono do produto: isso **nunca soma** em `GET /api/v1/me/progress` —
`ATV001` (a disciplina de 90h da matriz real) continua sendo o que de fato
conta como aprovado; as duas fontes não se tocam. Ver
[`HORAS_COMPLEMENTARES.md`](docs/dominio/HORAS_COMPLEMENTARES.md).

**Ainda não existe:** elegibilidade e pré-requisitos em runtime, simulação de
reprovação, curadoria por voto e `events` no `lifeos`, homologação da
coordenação para horas complementares, e o módulo `insights` inteiro. Lista
completa em [`SISTEMA.md`](docs/reports/SISTEMA.md) Parte XII.
