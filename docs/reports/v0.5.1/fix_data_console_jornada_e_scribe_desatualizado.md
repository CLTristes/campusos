# 📦 Documento de Entrega — CampusOS · v0.5.1

## Console de dados cobre a jornada + documentação de API atualizada

> **Data:** 19/09/2026
> **Branch:** `fix/data-console-modulo-journey`
> **Escopo:** dois bugs de operação encontrados pelo dono do produto — o `/data-console` não mostrava nenhum dado do módulo `journey`, e a spec do Scribe (`/docs/api`) estava congelada em 19/09 04:06, antes de `signup`, `verify-email` e `notes` existirem.
> **Qualidade:** 164 testes / 496 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

Quem entra no console de dados agora vê `Student`, `Registration`, `SubjectEnrollment` e `EnrollmentRequest` — os quatro models que a jornada do aluno produziu desde v0.2.0 e que nunca tiveram Resource — e `/docs/api` reflete os 16 endpoints reais do sistema, não os 6 que existiam em setembro de manhã.

## 1 — Por que o console estava cego pra jornada

`DataConsolePanelProvider::discoverResources()` aponta para `app/Filament/Resources/` (convenção deste projeto — Resources são ferramenta de dev/QA, não domínio, então vivem no app host, não nos módulos). Os 11 Resources existentes (catalog + tenancy) foram criados em v0.1.0, **antes** de `journey` existir (v0.2.0) — ninguém esqueceu de gerar os quatro que faltavam, eles simplesmente não existiam ainda quando o console nasceu, e nenhuma entrega seguinte fechou essa lacuna.

Criei os quatro Resources seguindo exatamente o padrão já estabelecido pelos 11 existentes (mesmo layout `Resource`/`Schemas/Form`/`Tables/Table`/`Pages`, FKs como `TextInput` cru, enums como `Select::options(EnumClass::class)`, `TrashedFilter` + `SoftDeletingScope` em quem usa `SoftDeletes`):

| Resource | Grupo de navegação | Observação |
| --- | --- | --- |
| `StudentResource` | Jornada do aluno | — |
| `RegistrationResource` | Jornada do aluno | `reg_status` via `Select` (`RegistrationStatus`) |
| `SubjectEnrollmentResource` | Jornada do aluno | `sen_status`/`sen_source` via `Select` |
| `EnrollmentRequestResource` | Jornada do aluno | **sem** `TrashedFilter`/`SoftDeletingScope` — `EnrollmentRequest` não usa `SoftDeletes` |

`EnrollmentRequestForm` expõe `erq_extraction` (o JSON que o Gemini devolveu) num `Textarea`, com `afterStateHydrated`/`dehydrateStateUsing` fazendo o round-trip JSON↔array explicitamente — sem isso, salvar re-serializaria a string dentro do cast `array` do model e corromperia o dado. É o mesmo padrão de risco que `OfferingForm::ofr_schedule` já tinha (também um `array` cast editado como texto cru) — não mexi nele por estar fora do escopo pedido, mas registro como achado.

## 2 — O teste que a lição "renderizar não é opinião" já pedia

`docs/reports/SISTEMA.md` Parte IX já registra a lição: *"testar o método não substitui renderizar a página"* — `canAccessPanel()` passava com o painel quebrado. Segui a mesma disciplina aqui: além de estender "as listagens do console abrem sem erro" com as 4 rotas novas, adicionei um teste que **abre a tela de edição de cada Resource novo com dado real**, incluindo um `EnrollmentRequest` com `erq_extraction` preenchido — é o cenário que provaria (ou não) o bug de double-encode do JSON antes de alguém encontrá-lo ao vivo.

## 3 — Scribe regenerado, sem tocar em API externa

`composer docs` (= `scribe:generate`) roda as rotas reais localmente — não fez nenhuma chamada ao Gemini nem a serviço externo algum. `storage/app/private/scribe/openapi.yaml` e `postman.json` (servidos ao vivo por `routes/web.php`, não versionados) agora têm os 16 endpoints reais; `.scribe/endpoints/` e `resources/views/scribe/index.blade.php` (versionados) foram atualizados junto.

## Ressalvas honestas

1. **`OfferingForm::ofr_schedule`** tem o mesmo risco de double-encode de JSON que eu corrigi em `EnrollmentRequestForm::erq_extraction`, só que mais antigo — não toquei por estar fora do pedido desta entrega.
2. **`UserForm` não tem campo para `usr_verification_code`/`usr_verification_code_expires_at`** (colunas adicionadas em v0.4.0, depois do Resource ter sido gerado) — não é uma falha de segurança (o campo simplesmente não aparece, nunca é tocado), mas um operador não consegue inspecionar o código de verificação pendente de um aluno pelo console. Fora do escopo pedido (era sobre `journey`).
3. **`lifeos.Note`** (v0.5.0, entregue nesta mesma sessão) também não tem Resource no console — o pedido foi especificamente sobre `journey`; registro para não repetir o mesmo tipo de lacuna decisão a decisão.
4. **Nenhum Resource novo tem tela de detalhe somente-leitura** (padrão que o FibroMais adotou, `ViewRecord` + `infolist()`) — os 11 Resources originais também não têm; mantive paridade com o padrão existente do CampusOS, não importei convenção de outro projeto sem pedido.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | Parte X: "11 recursos" → "15 recursos" no `/data-console` |
| `CLAUDE.md` | Nenhuma menção específica ao número de Resources — conferir se precisa |
