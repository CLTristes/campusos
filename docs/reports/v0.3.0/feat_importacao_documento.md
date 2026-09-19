# 📦 Documento de Entrega — CampusOS · v0.3.0 (iteração 3)

## Importação do documento acadêmico por IA

> **Data:** 19/09/2026
> **Branch:** `feat/importacao-documento`
> **Escopo:** bloco B4 do plano de ataque. Provedor de IA definido pelo dono do produto: **Gemini** (camada gratuita do Google AI Studio cobre o MVP).
> **Qualidade:** 140 testes / 409 asserts verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O aluno sobe o histórico escolar, uma IA lê, ele confere na tela e confirma — e
a graduação inteira dele aparece no sistema.

## 1 — O contrato, que é o ponto da arquitetura

`core/src/Contracts/AcademicDocumentExtractor.php` + os DTOs `ExtractedDocument`
e `ExtractedLine`.

O `journey` **não conhece o Google**. Ele fala com um contrato; quem decide a
implementação é um bind em `IntegrationsServiceProvider`, lido de
`DOCUMENT_EXTRACTOR`. Trocar Gemini por Claude, por um OCR local ou por um
parser determinístico é escrever outra classe e mudar uma variável de ambiente —
**zero linha de domínio, zero teste de domínio reescrito**.

Verificado por `grep` no fechamento: nenhuma menção a Gemini, Google ou `Http::`
dentro de `app-modules/journey/src/`, e nenhuma menção a `CampusOs\Journey`
dentro de `app-modules/integrations/src/`.

**Os DTOs são planos e sem regra de propósito.** `ExtractedLine::status` é o
texto impresso no documento ("Aprovado Por Nota/Frequência"), não um enum.
Traduzir é trabalho do `journey` (`DocumentStatusTranslator`) — assim, trocar de
provedor não arrasta o vocabulário acadêmico junto.

## 2 — A implementação Gemini

`integrations/src/Extractors/GeminiDocumentExtractor.php` + o prompt e o schema
separados em `Prompts/AcademicDocumentPrompt.php`.

- **HTTP direto, sem SDK.** A API é um POST com JSON, e o client do Laravel já
  dá timeout, retry e `Http::fake`. Um SDK seria dependência a mais para um
  endpoint só.
- **Saída estruturada por `responseSchema`**, não por pedido educado no prompt.
  O modelo devolve JSON que casa com a estrutura declarada — sem "responda só
  JSON, por favor" e sem parser tolerante do nosso lado.
- **`temperature: 0.0`** — extração é transcrição: queremos a leitura mais
  provável, sempre a mesma para o mesmo documento.
- **`NullDocumentExtractor`** roda nos testes (nenhum teste de domínio depende
  de rede) e é o plano B da demonstração: com `DOCUMENT_EXTRACTOR=null` o fluxo
  inteiro continua funcionando pela tela de conferência.

## 3 — A fronteira transporte × negócio

A distinção que estes testes mais protegem:

| Situação | Tratamento | Por quê |
| --- | --- | --- |
| 5xx, timeout, **429 de cota**, credencial ausente | `ExtractorUnavailableException` → **sobe**, a fila reprocessa | Marcar o documento como ilegível apagaria o upload do aluno por uma instabilidade de 30 segundos — ou por um limite nosso |
| "não é documento acadêmico", "nenhuma disciplina encontrada" | Volta no DTO como `failureReason` → estado **final** | Tentar de novo daria o mesmo resultado |

**429 merece nota:** num provedor gratuito é o erro mais provável de todos, e é
transporte.

## 4 — O fluxo: sobe → confere → confirma

`enrollment_requests` + `ParseAcademicDocumentJob` + duas Actions + três rotas.

**A IA nunca escreve matrícula.** Ela preenche `erq_extraction`, o aluno revisa,
e só `POST .../confirm` cria `subject_enrollments`. É o que transforma um erro
de leitura numa correção de 30 segundos em vez de um histórico corrompido — e é
o que torna aceitável usar IA num dado sensível.

O Job respeita as três disciplinas da regra de ouro nº 7: `TenantContext::runAs()`
envolvendo tudo, transporte que sobe, e guard de idempotência no topo
(reprocessar um documento já confirmado sobrescreveria a conferência manual).

**Linha cujo código não existe no catálogo vira pendência, não erro fatal** —
histórico real tem disciplina extinta, e derrubar a importação inteira por causa
de uma linha de 2019 é o pior dos mundos.

## 5 — Privacidade

- O arquivo vai para o disco `local`, nunca o público.
- `erq_file_path` e `erq_extraction` ficam **fora do diff de auditoria**: o
  caminho muda num deploy, e a extração é um JSON inteiro de dado pessoal. O que
  a trilha precisa registrar é a mudança de estado.
- `EnrollmentRequestResource` não expõe o caminho do arquivo, com teste.
- **Um aluno não lê o documento de outro**, mesmo dentro da mesma instituição.

## Ressalvas honestas

1. **Nunca rodou contra o Gemini de verdade.** Todos os testes usam `Http::fake`
   ou o extrator falso. O contrato da API foi escrito a partir da documentação
   do `generateContent`; **a primeira chamada real pode exigir ajuste** no
   formato de `inline_data` ou de `responseSchema`. Reserve 15 minutos para
   isso, com um PDF real e `GEMINI_API_KEY` preenchida.
2. **A qualidade da extração é desconhecida.** O histórico da UTFPR tem 6
   páginas e três tabelas de disciplina; o prompt pede explicitamente todas,
   mas só um teste real dirá se o modelo as lê por inteiro.
3. **A tela de conferência não existe** — só a API que a alimenta. Sem front, o
   fluxo se demonstra por `curl` ou pelo `/data-console`.
4. **`CreateRegistrationAction` ainda não tem endpoint.** A confirmação exige um
   `registration_id` que hoje só nasce por seeder ou teste. Se o aluno não tiver
   vínculo, o fluxo trava — **é o buraco mais provável de aparecer na demo**.
5. **`erq_kind` aceita `transcript` e `enrollment_request`, mas o tratamento é o
   mesmo.** O requerimento deveria criar matrículas `enrolled` (cursando) em vez
   de aproveitar a situação do documento; hoje depende do que vier em `status`.
6. **Sem limite de custo.** Cada upload é uma chamada de IA. Há `throttle:10,1`
   na rota, e nada além disso.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/dominio/DOCUMENTOS_ACADEMICOS.md` | Seção nova: como o documento vira dado (o contrato, o fluxo de 3 passos, transporte × negócio) |
| `docs/reports/SISTEMA.md` | `integrations` deixa de ser esqueleto; `enrollment_requests` na Parte V; o fluxo 4 da Parte VIII sai de "◇ próxima entrega" |
| `CLAUDE.md` | Mapa de módulos (`integrations` parcial), §Estado atual, fluxo canônico 4 |
| `docs/arquitetura/BANCO.md` | Ownership de `enrollment_requests` |
| `docs/arquitetura/COMUNICACAO.md` | Este é o primeiro contrato real do sistema — vale como exemplo canônico |
| `docs-site/features/matricula-e-progressao.html` | Fase 2 vira ✅ implementado, com o Gemini no lugar do Claude nos exemplos de código |
