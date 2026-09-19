# 📦 Documento de Entrega — CampusOS · v0.1.0 (iteração 1)

## Fundação, catálogo acadêmico e acesso

> **Data:** 19/09/2026
> **Branch:** `main` (entregue direto — ver ressalva 1)
> **Escopo:** blocos B0, B1 e B2 do [plano de ataque](../../../docs-site/plano-de-ataque.html) do HackLab UTFPR 2026, mais duas adições pedidas pelo dono do produto (documentação de API e console de dados)
> **Qualidade:** 69 testes / 256 asserts verdes · Pint verde · ArchTest verde

> ⚠️ **Report escrito retroativamente**, em 19/09 às 05:00, cobrindo quatro
> entregas que foram feitas sem ele. Não é uma fotografia do momento — é o
> registro honesto de uma dívida. A partir da v0.2.0 o ciclo `/nova-feature`
> é seguido inteiro, report incluso.

## O que mudou, em uma frase

O template genérico virou o CampusOS: namespace próprio, 6 módulos de domínio,
Postgres, a matriz curricular real da UTFPR em banco, login por token e um
console para ver o dado.

## 1 — Fundação (B0)

- Namespace `Modules\` → `CampusOs\`; vendor `modules/` → `campus-os/`.
- Módulos de exemplo (`orders`/`payments`/`notifications`) e os artefatos
  `[EXEMPLO]` do `core` removidos.
- Cinco módulos de domínio criados e registrados em `DOMAIN_MODULES`:
  `catalog`, `journey`, `lifeos`, `insights`, `integrations`.
- **Postgres 16 como banco do projeto** — `.env.example` e `compose.yaml` já
  apontam para `campusos/campusos`, sem bloco comentado de sqlite. Em
  desenvolvimento roda o Postgres do Homebrew (o daemon do Docker estava
  parado e subir um container custaria mais que usar o que já existia).

**Decisão:** os testes de isolamento de tenant e de auditoria foram
**reescritos** sobre `Campus` e `User` em vez de apagados junto com o model de
exemplo. A regra de ouro nº 9 diz que essas duas garantias nunca ficam sem
teste — e o teste de `$hidden` passou a cobrir um risco real (o hash da senha)
em vez de um campo inventado.

## 2 — Tenancy (B1)

- `campuses` e `users` como models de **domínio** (prefixo, UUID, SoftDeletes,
  `Entityable`, observer de auditoria). O `users` do esqueleto do Laravel saiu;
  sobraram só as tabelas de infraestrutura do framework.
- Enum `UserRole` com os quatro papéis reais: `student`, `coordinator`,
  `professor`, `institution_admin`.
- `LoginAction` no molde do `AbstractAction`, com Sanctum:
  `POST /api/v1/auth/login`, `GET /auth/me`, `POST /auth/logout`.
- Middleware `ResolveTenantFromUser` — a borda real, usada pelos **dois**
  canais (API por token, painel por sessão).

**Decisões e porquês:**

- **A `Entity` do template passa a significar a instituição de ensino, e a
  tabela NÃO foi renomeada.** `Entityable` e `EntityScope` do core dependem
  literalmente de `entity_ent_id`; trocar isso custaria uma hora de refactor
  para ganhar um nome melhor. Mesma decisão do FibroMais, pelo mesmo motivo.
- **O e-mail é único por instituição, não globalmente.** O login roda fora do
  escopo de tenant (é dele que o tenant sai) e, se o mesmo endereço existir em
  duas instituições, responde 422 pedindo `entity_id` em vez de escolher uma.
- **Senha errada e e-mail inexistente devolvem a mesma resposta** — diferenciar
  entregaria a lista de quem tem conta. Há teste comparando os dois corpos.

## 3 — Catálogo acadêmico (B2)

Dez tabelas: `courses`, `curricula`, `subjects`, `curriculum_subjects`,
`prerequisites`, `elective_groups`, `subject_equivalences`, `terms`,
`offerings` (+ `complementary_categories`, ainda não criada — ver ressalvas).

**O achado que reorganizou o modelo:** os três documentos reais do portal
derrubaram três premissas do desenho original. O total do curso **não** é a
soma de cinco faixas (3.240 h — número que a própria matriz calcula como
`SOMACH` e descarta) nem 2.940 h (extensão totalmente ortogonal). São
**3.000 h**, pela fórmula que o documento imprime:

```
CHTOTALPPC := SomaCHSemExt + (CHEXTENSAO - chext_discObrigatorias)
CHTOTALPPC := 2940 + (300 - 240) = 3000
```

Detalhe completo em [`DOCUMENTOS_ACADEMICOS.md`](../../dominio/DOCUMENTOS_ACADEMICOS.md).

**A matriz é gerada, não transcrita.** `parse_matriz_html.py` lê o HTML da tela
do portal e gera os dois CSVs que o seeder consome — 121 disciplinas e 98
equivalências, contra as 77 que couberam numa transcrição manual do PDF.

**O teste é o gabarito, e o gabarito não fomos nós que calculamos.** O rodapé
do documento imprime os totais de fechamento, então a importação é conferida
contra quatro somatórios independentes: CHT obrigatórias 2730, CHEXT
obrigatórias 240, CHEXT optativas 315 e a distribuição por período.

## 4 — Documentação de API e console de dados (pedidos do dono do produto)

- **Scribe 5.11 + UI Scalar em `/docs/api`**, no mesmo padrão do FibroMais:
  `type: laravel`, `add_routes: false`, e três rotas explícitas em
  `routes/web.php` (`/docs/api`, `openapi.yaml`, `postman.json`).
  `composer docs` regenera.
- **Filament 5.8 em `/data-console`**, 11 recursos em dois grupos de navegação.
  `ResolveTenantFromUser` no `authMiddleware` — sem ele o painel abriria vazio,
  porque o `EntityScope` filtraria por um tenant que ninguém definiu.

## Ressalvas honestas

1. **Estas quatro entregas foram feitas direto na `main`, sem branch e sem
   report** — o ciclo `/nova-feature` foi seguido pela metade (testes e
   documentação sim; branch, report e `sync-state` não). Este documento é a
   correção da dívida, não um álibi.
2. **`complementary_categories` está desenhada mas não existe em código.**
   Depende da resolução de atividades complementares do curso, que ainda não
   temos — sem ela o teto por categoria seria inventado.
3. **As optativas do CSV cobrem o conjunto 941 inteiro (75), mas o campo
   `prereq` de algumas pode estar incompleto**: o parser lê a coluna de
   pré-requisitos, e onde o portal deixou a célula vazia não há o que ler.
4. **O console de dados usa o guard `web` padrão**, com `canAccessPanel`
   restringindo a coordenação e à gestão. O FibroMais usa guard próprio
   (`data_console`) + MFA porque o painel dele roda em produção; aqui isso
   ficaria meia hora para proteger uma ferramenta que não sai do ambiente do
   hackathon. **Se o produto for para produção, vira requisito.**
5. **Um bug de contrato escapou para o usuário:** o painel estourava
   `FilamentManager::getUserName(): must be of type string, null returned`
   depois de um login bem-sucedido, porque o model declarava
   `getFilamentName()` sem implementar `Filament\Models\Contracts\HasName`. O
   teste que eu tinha escrito chamava `canAccessPanel()` e passava com o painel
   quebrado. Corrigido, e substituído por testes que **renderizam** as páginas.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | Ainda é o retrato do template. Precisa virar o do CampusOS: 6 módulos, 13 tabelas em código, os 7 endpoints, o console. |
| `CLAUDE.md` | §Visão (ainda tem o aviso "PREENCHIDO PELO PRONTUÁRIO"), mapa de módulos, §Estado atual, e os comandos novos (`composer docs`). |
| `docs/arquitetura/BANCO.md` | Tabela de ownership (13 tabelas novas) + diagrama ER. Hoje só lista as do template. |
| `docs/arquitetura/MODULOS.md` | Árvore dos 5 módulos novos. |
| `docs/dominio/` | `DOCUMENTOS_ACADEMICOS.md` já existe e está atualizado. Falta `ACESSO.md` (papéis, login, escopo de tenant). |
| `docs/README.md` | Mapa de leitura — citar o `DOCUMENTOS_ACADEMICOS.md` e o `docs-site/`. |
| `docs-site/` | Já atualizado a cada entrega (pedido explícito do dono do produto: nunca pode ficar desatualizado). |
