# 📦 Documento de Entrega — Template Laravel Modular · v0.0.1

## Nascimento do template: esqueleto modular + shared kernel + exemplo executável

> **Data:** 07/07/2026
> **Branch:** — (commit inaugural)
> **Escopo:** criar, a partir da arquitetura provada no projeto NFSe SaaS, um
> template genérico de sistema Laravel com arquitetura modular orientada a IA —
> pedido do dono do produto.
> **Qualidade:** **44 testes / 113 assertions verdes** · Pint verde · ArchTest
> verde (4 módulos de domínio + core).

---

## O que mudou, em uma frase

Nasceu um template Laravel 12 executável em que TODA a infraestrutura de sistema
(modularização com fronteiras testadas, multi-tenancy automático, auditoria
append-only, filas resilientes, Actions canal-agnósticas, disciplina de
docs/reports com versionamento de contexto e skills de IA) já vem pronta e
verde — restando ao sistema real apenas a lógica de negócio.

## 1. 🧱 Esqueleto e dependências

`composer create-project laravel/laravel:^12.0` + camada do template:

- `internachi/modular` ^3.0 (path repos em `app-modules/*`, namespace `Modules\`)
- `predis/predis` ^3.5 (Redis), `laravel/horizon` ^5.47 (workers; platform-fakes
  de `ext-pcntl`/`ext-posix` no composer.json para instalar no Windows)
- Pest ^4.7 no lugar do PHPUnit puro; Pint com `declare_strict_types`
- `compose.yaml` (PostgreSQL 16 + Redis 7), `.env.example` comentado
  (quickstart sqlite/database × produção pgsql/redis)

## 2. 🧠 Módulo `core` (shared kernel)

`TenantContext` (ponto único do tenant; `runAs`/`withoutScope` com restauração
garantida por `finally`), `EntityScope` + trait `Entityable` (filtro +
preenchimento automáticos), `AuditLog` + `AuditObserver` (diff before/after,
scrub de `$hidden`, best-effort, append-only), `AbstractAction` (template method
`final`), `HttpInputAdapter`/`McpInputAdapter`, comando `audit:query`.
Artefatos de fronteira do exemplo marcados `[EXEMPLO — REMOVÍVEL]`:
`PaymentGateway`, `OrderReadModel`, `PaymentResult`, `OrderSummaryDTO`,
`OrderPaid`, `PaymentGatewayUnavailableException`.

**Decisão:** `aud_user_id`/`aud_record_id` são `string` (não FK tipada) — o
template não impõe o tipo de PK do model de usuário do host. `audit_logs` sem
FKs — a trilha sobrevive aos registros.

## 3. 🏠 Módulo `tenancy` (mínimo real)

`Entity` (`entities`, `ent_`): raiz do multi-tenancy, sem `Entityable` (é a
própria raiz), auditada por `EntityObserver`, factory incluída. Deliberadamente
mínimo — User/papéis/onboarding nascem com o sistema real.

## 4. 🧪 Módulos de exemplo (`orders`, `payments`, `notifications`)

O fluxo canônico completo, funcional e testado, servindo de código de referência
para a IA copiar:

- **orders:** `PlaceOrderAction` (idempotência por `ref` com índice único
  `(entity_ent_id, ord_ref)` + 202 + dispatch na fila nomeada `orders`),
  `GetOrderAction`, `ProcessOrderPaymentJob` (`tries=5`, backoff exponencial,
  `TenantContext::runAs` primeiro, guard de reentrega, contrato, evento),
  model com TODAS as convenções, controller fino, resource (esconde
  `ord_internal_notes`), read model → DTO, rotas `/v1`.
- **payments:** `FakePaymentGateway` determinístico (999.99 = recusa de
  negócio; 666.66 = erro de transporte) + bind no provider.
- **notifications:** `SendOrderPaidNotification` (queued) + `Event::listen`
  explícito no provider.

## 5. 🚧 Fronteiras executáveis e suíte

`tests/Arch/ArchTest.php` parametrizado (`DOMAIN_MODULES`): strict_types em todo
módulo; core não depende de domínio; nenhum módulo importa o interno de outro.
Suítes `Unit`/`Feature`/`Modules`/`Arch` no `phpunit.xml` (sqlite `:memory:`,
fila `sync`). 44 testes cobrindo: template method, TenantContext, input
adapters, isolamento de tenant, auditoria (6 invariantes), fluxo HTTP (202,
idempotência, 422, 401, e2e sync, 404 cross-tenant, vazamento de colunas) e job
(3 desfechos + reentrega + runAs).

## 6. 🤖 Camada de IA e contexto

- **CLAUDE.md** — 10 regras de ouro (as 9 do NFSe genericizadas + a nº 10:
  nenhuma entrega sem report + sync-state).
- **docs/arquitetura/** — 8 documentos genericizados com o exemplo
  orders/payments.
- **docs/dominio/** — contrato do documento de domínio (preenchido pelo
  `/prontuario`).
- **docs/reports/** — este mecanismo: reports imutáveis + `SISTEMA.md`
  evergreen + **`sync-state.json`** (versionamento de contexto máquina-legível).
- **.claude/skills/** — `prontuario`, `dominio`, `nova-feature`, `sync-docs`,
  `novo-modulo`.

## 7. 🧰 Host enxuta

`ResolveTenantFromHeader` (placeholder didático de auth, alias `resolve.tenant`),
`config/models.php` (relações cross-módulo), `config/app-modules.php`, scripts
composer (`test`, `test:coverage`, `lint`, `lint:check`, `dev`).

## Ressalvas honestas

- **O middleware de tenant é um PLACEHOLDER** (header em claro) — está gritado
  no código, no CLAUDE.md e no README; produção exige token com hash.
- **Horizon não roda no Windows** (ext-pcntl) — instalado via platform-fakes;
  dev Windows usa `queue:work`. Documentado.
- **Sem CI incluído** — decisão de escopo; o workflow é trivial e está descrito
  no SISTEMA.md §XII.
- **Pest 4.x** (o NFSe usava 3.x) — API de `arch()` compatível com o que usamos;
  revisar presets ao estender.

## Impacto na documentação (para o /sync-docs)

- `SISTEMA.md`: criado já refletindo esta entrega. ✅
- `CLAUDE.md` §Estado atual: criado já refletindo esta entrega. ✅
- `sync-state.json`: entrada `v0.0.1/feat_bootstrap` **synced** (este report é o
  ponto zero do mecanismo).
