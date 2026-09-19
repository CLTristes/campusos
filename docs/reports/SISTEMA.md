# 📚 SISTEMA — CampusOS (Relatório Geral Consolidado)

> **Documento *evergreen*:** este é o retrato completo e SEMPRE-ATUAL do sistema.
> É atualizado pela skill `/sync-docs` a cada leva de reports pendentes
> (`sync-state.json`) — os relatórios de entrega (`vX.Y.Z/`) são as fotografias
> históricas; este é o filme montado.
>
> **Última sincronização:** 2026-09-19 · reports até `v0.7.0/feat_horas_complementares` ·
> por claude-sonnet-5

## Índice

- [Parte I — Visão geral](#parte-i--visão-geral)
- [Parte II — Stack e ambiente](#parte-ii--stack-e-ambiente)
- [Parte III — A arquitetura modular](#parte-iii--a-arquitetura-modular)
- [Parte IV — Passeio guiado pelos módulos](#parte-iv--passeio-guiado-pelos-módulos)
- [Parte V — A camada de dados](#parte-v--a-camada-de-dados)
- [Parte VI — Os três documentos que alimentam o sistema](#parte-vi--os-três-documentos-que-alimentam-o-sistema)
- [Parte VII — Multi-tenancy e auditoria](#parte-vii--multi-tenancy-e-auditoria)
- [Parte VIII — Os fluxos canônicos](#parte-viii--os-fluxos-canônicos)
- [Parte IX — Testes e qualidade](#parte-ix--testes-e-qualidade)
- [Parte X — Superfícies operacionais](#parte-x--superfícies-operacionais)
- [Parte XI — Como rodar](#parte-xi--como-rodar)
- [Parte XII — O que ainda não existe](#parte-xii--o-que-ainda-não-existe)

---

# Parte I — Visão geral

O **CampusOS** resolve dois problemas do HackLab UTFPR 2026 com um dado só:

- **Desafio 5.1 — jornada acadêmica.** O estudante não enxerga a própria
  trajetória: quanto falta, o que trava, quando termina.
- **Desafio 5.2 — integração na comunidade acadêmica** (cobertura). O
  conhecimento de quem já cursou a disciplina evapora quando a turma acaba.

**A matrícula é a chave dos dois.** No momento em que o sistema sabe que o aluno
está em *Cálculo 2, turma S71, 2026/1*, duas coisas acontecem de graça: a barra
de progresso anda (5.1), e ele passa a enxergar tudo que qualquer veterano
deixou público **naquela disciplina, em qualquer semestre** (5.2). Separá-los em
dois produtos seria manter duas cópias de `subject_enrollments`.

**Nenhuma integração com sistema acadêmico.** Todo dado entra por documento que
a própria universidade emite — o que permite o produto funcionar em qualquer
instituição sem negociar acesso a nada. Ver [Parte VI](#parte-vi--os-três-documentos-que-alimentam-o-sistema).

**Multi-tenant por instituição de ensino.** A `Entity` do template passa a
significar a universidade; curso, matriz e disciplina são dados. Piloto: UTFPR,
Câmpus Francisco Beltrão, curso 25 (Sistemas de Informação), matriz 45.

O desenho de produto completo — anterior ao código e ainda canônico para o que
não foi implementado — vive em [`docs-site/`](../../docs-site/index.html).

---

# Parte II — Stack e ambiente

| Peça | Escolha |
| --- | --- |
| PHP | 8.2+ (tipagem estrita em todo arquivo) |
| Framework | Laravel 12 |
| Banco | **PostgreSQL 16** — em dev roda o do Homebrew; `compose.yaml` sobe pg16+redis7 |
| Dados | Eloquent como única camada |
| Modularidade | `internachi/modular` — namespace `CampusOs\`, vendor `campus-os/` |
| Auth | Laravel Sanctum (token) |
| Testes | Pest — sqlite `:memory:`, fila `sync` |
| Estilo | Pint (preset laravel + `declare_strict_types`) |
| Docs de API | Scribe 5.11 + UI Scalar |
| Console de dados | Filament 5.8 |

---

# Parte III — A arquitetura modular

Modular monolith. O domínio vive em `app-modules/<módulo>/`, cada um um pacote
Composer com fronteira própria: **um módulo nunca importa a classe interna de
outro**. Falam por contratos, eventos e — o caso mais comum aqui — relações
Eloquent resolvidas por `config('models.*')`.

O `tests/Arch/ArchTest.php` quebra o CI se a fronteira for furada. Os seis
módulos de domínio estão em `DOMAIN_MODULES`.

---

# Parte IV — Passeio guiado pelos módulos

## IV.1 `core` — o shared kernel

Vem do template, praticamente intocado: `AbstractAction` (o template method
sanitize→validate→authorize→handle), `Entityable`, `EntityScope`,
`TenantContext`, `AuditLog` + `AuditObserver`, adaptadores de Input e o comando
`audit:query`.

## IV.2 `tenancy` — a instituição e quem entra

**3 tabelas:** `entities` (a universidade), `campuses`, `users`.

- `UserRole`: `student`, `coordinator`, `professor`, `institution_admin`.
- `LoginAction` (Sanctum) — o e-mail é único **por instituição**, então o login
  roda fora do escopo de tenant e, em ambiguidade, pede `entity_id`.
- `User` implementa `FilamentUser` + `HasName` para o console de dados.
- `SignupAction` — cadastro livre do **aluno**: a instituição é resolvida por
  `entities.ent_email_domain` (o domínio do e-mail), nunca escolhida pelo
  cliente. Acesso é imediato (token sai do próprio cadastro);
  `VerifyEmailAction`/`ResendVerificationCodeAction` confirmam um código de 6
  dígitos por e-mail como camada de segurança em paralelo, sem bloquear nada.

## IV.3 `catalog` — o esqueleto acadêmico

**10 tabelas:** `courses`, `curricula`, `subjects`, `curriculum_subjects`,
`prerequisites`, `elective_groups`, `subject_equivalences`, `terms`,
`offerings`, `complementary_categories` (B7 — o teto por categoria de
atividade complementar, embutido em `GET /courses/{course}/curriculum`).
Dados mestres: mudam por resolução de colegiado, nunca por ação de aluno.

A distinção que sustenta o módulo: **`subjects` é a disciplina** (global na
instituição, identidade única, onde o acervo se pendura) e
**`curriculum_subjects` é o lugar dela numa matriz**. A mesma Cálculo 1 é 3º
período numa matriz e 2º em outra, continuando a ser a mesma disciplina.

A matriz 45 da UTFPR está semeada: **121 disciplinas, 34 pré-requisitos, 98
equivalências**, geradas por `parse_matriz_html.py` a partir do HTML da tela do
portal.

## IV.4 `journey` — a trajetória do aluno

**5 tabelas em código:** `students`, `registrations`, `subject_enrollments`,
`enrollment_requests`, `complementary_activities` (B7).

- `ApprovalPolicy` — a regra de aprovação da UTFPR (ver
  [`PROGRESSAO.md`](../dominio/PROGRESSAO.md) regra 5).
- `ProgressReadModel` — a barra de progresso, **calculada, nunca armazenada**.
- `CreateRegistrationAction` — a matriz é resolvida no servidor e congelada.
- `ParseAcademicDocumentJob` + `UploadAcademicDocumentAction` +
  `ConfirmAcademicDocumentAction` — o caminho do documento até a matrícula.
- `DocumentStatusTranslator` — o texto impresso vira enum. Mora aqui, não no
  extrator: o provedor transcreve, o domínio interpreta.
- `ComplementaryHoursReadModel` (B7) — o teto por categoria de atividade
  complementar, calculado por categoria (nunca por certificado). **Nunca
  soma no `ProgressReadModel`** — decisão do dono do produto (ver
  [`PROGRESSAO.md`](../dominio/PROGRESSAO.md) pendência 3 e
  [`HORAS_COMPLEMENTARES.md`](../dominio/HORAS_COMPLEMENTARES.md)): `ATV001`
  (a disciplina de 90h da matriz real) é o que de fato conta como aprovado;
  `complementary_activities` é só o tracker pessoal do aluno.

## IV.5 `integrations` — os adaptadores externos

`GeminiDocumentExtractor` (Google AI Studio, camada gratuita) e
`NullDocumentExtractor`, ambos implementando `AcademicDocumentExtractor` do
`core`. O bind em `IntegrationsServiceProvider` é **o único lugar do sistema que
sabe qual provedor de IA lê os documentos**.

HTTP direto, sem SDK: a API é um POST com JSON, e o client do Laravel já dá
timeout, retry e `Http::fake`. Saída estruturada por `responseSchema`, não por
pedido no prompt.

**Validado contra a API real** (modelo `gemini-3.6-flash` — `gemini-2.0-flash`
foi descontinuado) com o histórico e a matriz reais do dono do produto: as 57
linhas do histórico bateram 100% com as seis tabelas do documento (obrigatórias,
optativas, enriquecimento, exame de suficiência, disciplinas matriculadas), e
todo `"*"` de frequência voltou `null`. A camada gratuita devolveu um 503
transitório em parte das chamadas — tratado como transporte, a fila reprocessa.

## IV.6 `lifeos` (B5+B6 em código) · `insights` (esqueleto)

`lifeos` tem o acervo do veterano (desafio 5.2, B5): `Note` + `Visibility`
(enum de 5 níveis: private/offering/subject/course/institution) +
`NoteVisibilityScope` (global scope aplicado a toda leitura de `Note`). A
regra que faz a nota atravessar semestres é a ausência de filtro por
`term_trm_id` na leitura — ver [`ACERVO.md`](../dominio/ACERVO.md).

B6 (tarefas da turma) também está em código: `Task` reaproveita o MESMO enum
`Visibility` para `tsk_visibility` — mesmo vocabulário, sem repetir a
armadilha do LifeOS original (dois enums "quase iguais" por tabela). Ao
contrário de `Note`, uma tarefa não nasce sempre privada — cadastrar já com
`visibility: offering` é o próprio ato de compartilhar com a turma.
`AdoptTaskAction` copia a tarefa da turma para o aluno (`origin_tsk_id`
aponta pra origem, índice único `(owner_usr_id, origin_tsk_id)` garante
idempotência), e `GET /me/agenda` junta pendências próprias com o que a turma
compartilhou nas ofertas do **termo corrente** ainda não adotado — ver
[`TAREFAS.md`](../dominio/TAREFAS.md). Falta a curadoria por voto e `events`
(◎ esticada no desenho).

A fronteira com `journey` passa por `EnrolledSubjectsProvider` (contrato no
`core`, implementado em `JourneyEnrolledSubjectsProvider`): o `lifeos` nunca
lê `subject_enrollments` direto, e o `ArchTest` prova isso a cada build. A
pergunta "em quais ofertas o aluno está matriculado AGORA" (para a agenda) não
ganhou um método novo nesse contrato — é FK simples sem a regra de "atravessar
termo" que justificou o contrato original, respondida direto em
`AgendaReadModel` via `config('models.*')`.

`insights` continua esqueleto. **Não terá tabelas por desenho**: lê por read
model o que `journey` e `catalog` já possuem.

## IV.7 App host (`app/`) — só borda

Middlewares (`ResolveTenantFromHeader` placeholder, `ResolveTenantFromUser`
real), o `DataConsolePanelProvider` do Filament e os Resources do console.
Nenhuma regra de negócio.

---

# Parte V — A camada de dados

**21 tabelas em código.** Convenções sem exceção: prefixo de 3 letras em toda
coluna, PK UUID gerada na aplicação, `SoftDeletes` no que é transacional, FK no
formato `{tabela_singular}_{pk_origem}`, `entity_ent_id` em toda tabela com
escopo de tenant.

Ownership e ER completos em [`BANCO.md`](../arquitetura/BANCO.md).

**A tabela mais importante é `subject_enrollments`** — dela saem a progressão
(5.1) e o direito de ver o acervo (5.2). Índice único
`(registration, subject, term)`: cursar de novo em outro semestre gera linha
nova, que é como reprovação aparece no histórico.

**`notes`** é a tabela do acervo: três FKs nullable (`subject_sbj_id`,
`offering_ofr_id`, `course_crs_id`) — cada nível da escada de visibilidade usa
uma delas ou nenhuma — e `nte_visibility` default `private`.

**`complementary_categories`** (catalog) guarda o teto por categoria
(`ccg_max_hours`), sem `SoftDeletes` — dado mestre, mesmo padrão de
`curriculum_subjects`. **`complementary_activities`** (journey) é o tracker
do aluno: `cac_hours_claimed` é o que o certificado declara,
`cac_hours_granted` fica reservado para a homologação manual futura (sempre
`null` nesta entrega) — o corte automático pelo teto é calculado agregado
por categoria em `ComplementaryHoursReadModel`, nunca gravado por linha.

**`tasks`** é a tabela da tarefa da turma (B6): mesmas FKs de escopo de
`notes` mais `origin_tsk_id` (auto-relacionamento — a cópia de uma adoção
aponta pra origem) e `project_prj_id` (sem FK ainda: `projects` é ◎ esticada,
sem tabela). Índice único `(owner_usr_id, origin_tsk_id)` é a guarda de
idempotência da adoção; `tsk_visibility` default `private`, mas ao contrário
de `notes` pode nascer em qualquer nível já na criação.

---

# Parte VI — Os três documentos que alimentam o sistema

| Quem | Documento | O que o sistema ganha |
| --- | --- | --- |
| Coordenação | **Consulta Curso e Matriz Curricular** | O catálogo inteiro, com pré-requisitos e equivalências |
| Aluno | **Histórico escolar** | Todo o passado dele, com situação por disciplina |
| Aluno | **Requerimento de matrícula** | O semestre corrente + a grade de horários |

Estrutura completa (colunas, vocabulário, quadros de resumo, a fórmula das
cargas horárias) em [`DOCUMENTOS_ACADEMICOS.md`](../dominio/DOCUMENTOS_ACADEMICOS.md).

**O número que define tudo:** o total a integralizar da matriz 45 é **3.000 h**,
pela fórmula que o próprio documento imprime —
`CHTOTALPPC := 2940 + (300 − 240)`. A extensão é ortogonal até onde cabe dentro
das disciplinas; o que sobra vira exigência autônoma que soma.

---

# Parte VII — Multi-tenancy e auditoria

**Tenant** — `Entityable` + `EntityScope` filtram por `TenantContext`, populado
na borda por `ResolveTenantFromUser` (API e painel) ou restaurado com
`runAs()` em seeders, jobs e CLI.

> **Armadilha registrada:** `WithoutModelEvents` num seeder desliga o hook
> `creating` do `Entityable` — todo seed multi-tenant falha com
> `null value in column entity_ent_id`.

**Auditoria** — todo model de domínio tem observer estendendo `AuditObserver`.
O hash de senha e o `remember_token` ficam fora do diff (`$hidden` do observer)
**e** fora do Resource, com teste para os dois.

---

# Parte VIII — Os fluxos canônicos

**1. Implantação (coordenação).** Sobe o documento da matriz → catálogo semeado.
Hoje via `MatrizUtfprSeeder` lendo CSV gerado do HTML; a leitura por IA é o B4.

**2. Login.** `POST /api/v1/auth/login` → `LoginAction` (fora do escopo de
tenant) → token Sanctum. Toda rota autenticada passa por
`auth:sanctum` + `tenant.user`.

**3. Progressão.** `GET /api/v1/me/progress` → `ProgressReadModel` → três faixas
(2730 + 210 + 60 = 3000 h), pendentes por período e previsão pelo ritmo real.

**4. Importação de documento do aluno.** `POST /api/v1/me/academic-documents`
→ **202** + `ParseAcademicDocumentJob` na fila `imports` → o extrator lê →
`erq_status` vira `parsed` → o aluno revisa na tela de conferência →
`POST .../confirm` cria as `subject_enrollments`.

`registration_id` na confirmação é **opcional**. Sem ele,
`ConfirmAcademicDocumentAction::resolveRegistration()` acha ou cria o `Student`
do usuário autenticado, reaproveita um vínculo já existente no mesmo curso, ou
cria um novo via `CreateRegistrationAction` usando curso, RA e semestre de
ingresso do `meta` que a IA leu do cabeçalho — o aluno nunca digita essa
informação de novo. Sem `course_code`/`entry_term` reconhecíveis no `meta`, a
Action falha pedindo o vínculo explícito, em vez de adivinhar.

**A IA nunca escreve matrícula.** E a fronteira transporte × negócio é rígida:
5xx, timeout, 429 de cota e credencial ausente **sobem** (a fila reprocessa);
"não é documento acadêmico" vira estado final. Num provedor gratuito o 429 é o
erro mais provável, e tratá-lo como documento ruim apagaria o upload do aluno.

---

# Parte IX — Testes e qualidade

**184 testes / 557 asserções verdes** · Pint verde · ArchTest verde.

O padrão que mais rende aqui: **o gabarito não fomos nós que calculamos.** O
rodapé do documento da matriz imprime os totais de fechamento, então a
importação é conferida contra quatro somatórios independentes (2730, 240, 315 e
a distribuição por período). O mesmo vale para a regra de aprovação, testada
contra sete pares (média, frequência, situação) do histórico real.

**Três lições de teste registradas:**

1. Testar o método não substitui renderizar a página. `canAccessPanel()` passava
   com o painel quebrado por falta do contrato `HasName`.
2. O guard do Laravel cacheia o usuário dentro do mesmo teste — um token
   revogado ainda passa sem `forgetGuards()`.
3. Validação de tamanho de string em campo que só transcreve documento é
   armadilha: `lines.*.status` com `max:64` derrubava a confirmação **inteira**
   por causa de uma única situação administrativa do histórico real ("Enade -
   Estudante Dispensado..." com 84 caracteres), quando deveria virar pendência
   de UMA linha, não erro fatal de todas. Só apareceu testando com o documento
   de verdade — subiu para `max:255`.

---

# Parte X — Superfícies operacionais

| Rota | O que é |
| --- | --- |
| `/docs/api` | Documentação interativa (Scribe → OpenAPI → UI Scalar). `composer docs` regenera |
| `/docs/api/openapi.yaml` · `postman.json` | A spec e a coleção |
| `/data-console` | Console de dados (Filament), 15 recursos (catalog + tenancy + journey). Só coordenação e gestão |
| `/up` | Health check |

**22 endpoints** na spec: `auth/{login,signup,me,logout,verify-email,
verify-email/resend}`, `courses`, `courses/{id}/curriculum`, `me/progress`, os
três de `me/academic-documents` (enviar, consultar, confirmar), os quatro de
`notes` (listar, criar, ver, mudar visibilidade), `me/agenda` e os três de
`tasks` (criar, adotar, mudar status) — B6 — e os dois de
`me/complementary-activities` (listar+resumo, declarar) — B7.

---

# Parte XI — Como rodar

```bash
composer install
cp .env.example .env && php artisan key:generate

# Postgres: o compose.yaml sobe pg16+redis7, ou use um Postgres local
createuser campusos --createdb && createdb campusos -O campusos

php artisan migrate:fresh --seed     # matriz 45 da UTFPR semeada
composer test                        # 140 testes verdes
php artisan serve
```

**Logins de desenvolvimento** (senha `campusos`):
`aluno@alunos.utfpr.edu.br` · `coordenacao@utfpr.edu.br` (este entra no console).

---

# Parte XII — O que ainda não existe

| Área | O que falta |
| --- | --- |
| `journey` | Elegibilidade/pré-requisitos em runtime, simulação de reprovação, cálculo do período por déficit de CHS, homologação da coordenação para atividades complementares (declaração já existe, B7) |
| `lifeos` | Curadoria por voto e `events` (◎ esticada) — o acervo de notas (B5) e as tarefas da turma (B6) estão prontos |
| `insights` | Tudo — os agregados da coordenação |
| Transversal | CCE autônomo (as 60 h de extensão que ninguém consegue cumprir pelo sistema), RBAC granular, guard próprio do console |

---

### Changelog deste documento

| Data | Mudança | Fonte |
| --- | --- | --- |
| 2026-07-07 | Criação — retrato inicial do template | `v0.0.1/feat_bootstrap` |
| 2026-09-19 | **Reescrito como retrato do CampusOS** — 6 módulos, 16 tabelas, os três documentos acadêmicos, login e console | `v0.1.0/feat_fundacao_catalogo_e_acesso` |
| 2026-09-19 | Módulo `journey`: vínculo, histórico, `ApprovalPolicy` e a barra de progresso | `v0.2.0/feat_journey_progressao` |
| 2026-09-19 | Importação de documento por IA: contrato no `core`, Gemini no `integrations`, fluxo sobe→confere→confirma | `v0.3.0/feat_importacao_documento` |
| 2026-09-19 | Gemini validado contra a API real (modelo trocado para `gemini-3.6-flash`); confirmação cria o vínculo sozinha a partir do documento; `lines.*.status` de 64 para 255 caracteres | `v0.3.1/feat_gemini_real_e_vinculo_automatico` |
| 2026-09-19 | Cadastro livre do aluno: instituição resolvida por `ent_email_domain`, acesso imediato, verificação por código em paralelo | `v0.4.0/feat_cadastro_aluno_com_verificacao_de_email` |
| 2026-09-19 | O acervo do veterano (B5): escada de visibilidade de 5 níveis, `EnrolledSubjectsProvider` como fronteira com o `journey` | `v0.5.0/feat_acervo_do_veterano` |
| 2026-09-19 | Console de dados ganha os 4 Resources da jornada (11→15); Scribe regenerado | `v0.5.1/fix_data_console_jornada_e_scribe_desatualizado` |
| 2026-09-19 | Tarefas da turma (B6): `Task` reaproveita o enum `Visibility` de `notes`; adotar copia (`origin_tsk_id` + índice único de idempotência); `GET /me/agenda` filtra pelo termo corrente — ao contrário do acervo, aqui a ausência de filtro seria o bug | `v0.6.0/feat_tarefas_da_turma` |
| 2026-09-19 | Horas complementares (B7): teto por categoria (`complementary_categories`, catalog) + tracker do aluno (`complementary_activities`, journey); corte calculado agregado por categoria, nunca por certificado; decisão do dono do produto — nunca soma no `ProgressReadModel` (`ATV001` continua sendo o que conta) | `v0.7.0/feat_horas_complementares` |
