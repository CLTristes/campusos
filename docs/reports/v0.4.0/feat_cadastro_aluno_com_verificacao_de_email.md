# 📦 Documento de Entrega — CampusOS · v0.4.0

## Cadastro livre do aluno, com verificação de e-mail por código

> **Data:** 19/09/2026
> **Branch:** `feat/cadastro-aluno-com-verificacao-de-email`
> **Escopo:** fecha a pendência nº 1 de `docs/dominio/ACESSO.md` e a nº 4 de `docs-site/index.html` — "como o aluno prova que é aluno?"
> **Qualidade:** 152 testes / 463 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O aluno se cadastra sozinho com o e-mail institucional, recebe acesso imediato e um código de verificação por e-mail — sem lista de universidades pra escolher e sem depender de seeder para existir no sistema.

## 1 — A instituição nasce do domínio do e-mail, não de uma escolha

`entities.ent_email_domain` (nova coluna, nullable, única) guarda o domínio que autoriza cadastro livre — ex.: `alunos.utfpr.edu.br` para a UTFPR. Essa coluna já era **citada como existente** em `docs/dominio/ACESSO.md` (pendência nº 1), mas nunca tinha sido criada de verdade: conferi a migration e o model antes de escrever qualquer código, e não existia. A pendência estava certa sobre a intenção, errada sobre o estado — fechei os dois.

`SignupAction::handle()` extrai o domínio do e-mail (`Str::after($email, '@')`), acha a `Entity` correspondente **fora do escopo de tenant** (mesma postura do `LoginAction` — ainda não existe tenant no contexto neste momento) e recusa com 422 se nenhuma instituição tiver aquele domínio habilitado. O cliente nunca escolhe a instituição.

## 2 — Acesso é imediato; a verificação é uma camada em paralelo, não um portão

Decisão do dono do produto: o cadastro já devolve o token Sanctum, igual ao login. O aluno usa o sistema na hora — a `pendência` que resta pra ele é subir o histórico (v0.3.1), não confirmar o e-mail.

O código de verificação (6 dígitos, expira em 15 minutos) é gerado em `SignupAction`, guardado **hashado** (`usr_verification_code`, cast `hashed` — mesmo tratamento de `usr_password`) e enviado por `VerificationCodeMail`. `VerifyEmailAction` confere hash + validade e marca `usr_email_verified_at`; `ResendVerificationCodeAction` gera um código novo se o anterior expirar. Nenhuma das duas Actions bloqueia rota nenhuma — é o que "camada de segurança em paralelo" significa na prática.

## 3 — O que existe agora

| Rota | Autenticação | O que faz |
| --- | --- | --- |
| `POST /api/v1/auth/signup` | pública, `throttle:6,1` | Cria a conta, resolve a instituição pelo domínio, devolve token |
| `POST /api/v1/auth/verify-email` | Sanctum | Confere o código de 6 dígitos |
| `POST /api/v1/auth/verify-email/resend` | Sanctum, `throttle:3,10` | Gera e reenvia um código novo |

`UserResource` ganhou `email_verified` (booleano) — é o que o front usa pra decidir se mostra o aviso de "confirme seu e-mail".

## 4 — Duas colunas sensíveis, o mesmo cuidado da senha

`usr_verification_code` está no `$hidden` do **model** (nunca serializa) **e** no `$hidden` do `UserObserver` (nunca entra no diff de auditoria) — os dois precisam existir independentemente, e os dois têm teste (mesmo padrão já registrado em `SISTEMA.md` Parte VII para `usr_password`).

## 5 — E-mail sem SMTP configurado

`MAIL_MAILER=log` continua sendo o padrão do `.env` — nenhuma configuração de SMTP foi necessária para este report. Em dev/demo, o código de verificação aparece em `storage/logs/laravel.log`. `VerificationCodeMail` não usa view/Blade (só `Content::htmlString`), então não precisou registrar namespace de views para o módulo `tenancy`.

## Ressalvas honestas

1. **Sem front, a verificação se demonstra por `curl`/Postman e pelo log do Mailer** — não há tela nenhuma que peça o código ainda.
2. **`ent_email_domain` cobre só o domínio DO ALUNO.** A UTFPR usa `utfpr.edu.br` para coordenação/professores — cadastro livre continua fechado para esses papéis (correto: só aluno se auto-cadastra; os outros continuam nascendo por seeder/coordenação).
3. **Sem SMTP real configurado**, não dá para provar que um e-mail de verdade chega numa caixa de entrada de verdade — só que o sistema *tentaria* enviar (Mailable montado, `Mail::to()` chamado). Configurar um provedor (Mailtrap, SES, etc.) fica para quando o front precisar mostrar isso ao vivo.
4. **Não há limite de cadastros por domínio nem verificação de que o e-mail realmente existe** (sem SMTP real, nem uma verificação de entrega faria sentido). O `throttle:6,1` na rota é a única barreira contra automação hoje.
5. **RBAC continua sendo só o papel** (`UserRole`) — o cadastro sempre cria `student`; não há caminho de auto-cadastro para outros papéis, por design.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/dominio/ACESSO.md` | Pendência nº 1 fechada → vira regra numerada; `Mapa de código` ganha uma linha para `SignupAction`/`VerifyEmailAction`/`ResendVerificationCodeAction` |
| `docs/reports/SISTEMA.md` | Parte IV.2 (tenancy): cadastro livre; Parte X: 3 endpoints novos (12 no total); Parte XII: remover a pendência de sign-up |
| `CLAUDE.md` | §Estado atual: "não existe cadastro" deixa de ser verdade |
| `docs-site/index.html` | Pendência nº 4 já marcada ✅ nesta entrega (não é preciso mexer de novo) |
