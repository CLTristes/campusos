# Acesso — quem entra, no quê, e com qual recorte

> **Área:** `tenancy` · **Última sincronização:** v0.4.0 · 2026-09-19

## O problema

Três tipos de gente diferente precisam do sistema: o **aluno** (que só enxerga a
própria vida acadêmica), a **coordenação** (que enxerga agregados do curso, nunca
aluno nomeado) e a **gestão da instituição**. Nenhum deles pode ver dado de outra
universidade — histórico escolar é dado pessoal sensível.

## As regras

### 1. O tenant é a instituição de ensino

A `Entity` do template significa **a universidade**. A tabela não foi renomeada
porque `Entityable` e `EntityScope` do `core` dependem literalmente de
`entity_ent_id`.

**Curso não é tenant.** Dois cursos da mesma universidade precisam enxergar o
mesmo catálogo de disciplinas (Cálculo 1 é a mesma disciplina para Engenharia e
para Química) — e é isso que permite o acervo do desafio 5.2 atravessar cursos.

### 2. Câmpus é recorte interno

A UTFPR tem 13 câmpus, e **o mesmo curso em câmpus diferentes tem matriz
diferente**. Oferta e turma também são por câmpus.

### 3. Quatro papéis

| Papel | Enxerga |
| --- | --- |
| `student` | a própria vida acadêmica |
| `coordinator` | agregados do curso; entra no console de dados |
| `professor` | (ainda sem superfície própria) |
| `institution_admin` | a instituição inteira; entra no console |

RBAC granular por permissão (tabela `permissions`, como no FibroMais) é
◇ planejado. **Hoje, permissão é o papel** — e isso está declarado no enum
`UserRole`, com `seesInsights()` e `managesCatalog()` em vez de `if` espalhado.

### 4. O e-mail é único por instituição, não globalmente

Índice único `(entity_ent_id, usr_email)`. Consequência direta: **o login roda
fora do escopo de tenant** — neste momento ainda não há tenant no contexto, e é
justamente do usuário encontrado que ele sai.

Se o mesmo e-mail existir em mais de uma instituição, o login é **ambíguo** e
responde 422 pedindo `entity_id`, em vez de escolher uma por conta própria.

### 5. Senha errada e e-mail inexistente dão a mesma resposta

Diferenciar entrega a um atacante a lista de quem tem conta. Há teste
comparando os dois corpos de resposta.

### 6. O token carrega a instituição, o cliente não

`ResolveTenantFromUser` popula o `TenantContext` a partir do **usuário
autenticado** — nunca de um parâmetro do request. Vale para os dois canais: API
(token Sanctum) e console (sessão web). Uma borda só.

### 7. Logout revoga apenas o token usado

Os outros dispositivos seguem logados. É o comportamento que as pessoas esperam,
e o contrário assusta.

### 8. O console de dados não é tela de produto

`/data-console` é ferramenta de dev/QA. Só `coordinator` e `institution_admin`
entram (`User::canAccessPanel`); estudante **nunca**, mesmo sendo usuário válido
da API. O painel não tem recorte por curso — quem entra vê a instituição inteira.

> **Se o produto for para produção, isto vira requisito:** guard próprio
> (`data_console`, fora do RBAC de domínio) + MFA obrigatória, como no FibroMais.
> Hoje usa o guard `web` padrão porque o painel não sai do ambiente do hackathon.

### 9. O aluno se cadastra sozinho — a instituição vem do domínio do e-mail

`entities.ent_email_domain` guarda o domínio que autoriza cadastro livre do
**aluno** (ex.: `alunos.utfpr.edu.br` — não o domínio da coordenação/staff,
que continua nascendo só por seeder). `SignupAction` extrai o domínio de
`Str::after($email, '@')`, acha a instituição fora do escopo de tenant (mesma
postura do login) e recusa com 422 se nenhuma instituição tiver aquele domínio
habilitado. O cliente nunca escolhe a instituição numa lista.

**Acesso é imediato**: o cadastro já devolve o token Sanctum, igual ao login.
A verificação por e-mail (código de 6 dígitos, expira em 15 minutos,
guardado hashado como `usr_password`) é uma camada de segurança **em
paralelo**, não um portão — nenhuma rota depende de `usr_email_verified_at`
hoje. Despublicar não existe aqui, mas o padrão é o mesmo do resto do
domínio: nada bloqueia por padrão além do que foi decidido explicitamente.

## ⚠️ Pendências do dono do produto

1. **O `professor` não tem superfície.** O papel existe no enum e nada o usa —
   `offerings.ofr_professor_name` é texto, não FK. Decidir se o docente entra no
   produto ou se o papel sai do enum.
2. **Admin de plataforma.** `users.entity_ent_id` é NOT NULL, então não há como
   existir um usuário sem instituição. Quando entrar, a coluna vira nullable e o
   `EntityScope` ganha a exceção explícita, com teste próprio.

## Mapa de código

| Regra | Onde vive | Teste |
| --- | --- | --- |
| 1, 2 | `tenancy/src/Models/{Entity,Campus}.php` | `EntityScopeTest` |
| 3 | `tenancy/src/Enums/UserRole.php` | `DataConsoleTest` |
| 4, 5 | `tenancy/src/Actions/LoginAction.php` | `AutenticacaoTest` |
| 6 | `app/Http/Middleware/ResolveTenantFromUser.php` | `AutenticacaoTest`, `DataConsoleTest` |
| 7 | `tenancy/src/Http/Controllers/AuthController.php` | `AutenticacaoTest` |
| 8 | `tenancy/src/Models/User.php::canAccessPanel` | `DataConsoleTest` |
| 9 | `tenancy/src/Actions/{Signup,VerifyEmail,ResendVerificationCode}Action.php` | `CadastroAlunoTest` |
