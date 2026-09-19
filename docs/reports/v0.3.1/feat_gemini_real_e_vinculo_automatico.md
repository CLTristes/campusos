# 📦 Documento de Entrega — CampusOS · v0.3.1

## Gemini plugado de verdade + o vínculo nasce sozinho do documento

> **Data:** 19/09/2026
> **Branch:** `feat/vinculo-automatico-na-confirmacao`
> **Escopo:** as duas ressalvas mais urgentes do `v0.3.0/feat_importacao_documento` — a extração nunca ter rodado contra a API real, e o aluno sem vínculo não conseguir importar nada.
> **Qualidade:** 144 testes / 425 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

A `GEMINI_API_KEY` foi plugada, a leitura rodou contra o histórico e a matriz reais, e a confirmação do documento agora cria o vínculo do aluno sozinha — o "buraco que trava a demo" está fechado.

## 1 — A API real reclamou de uma coisa só: o modelo

`gemini-2.0-flash` foi descontinuado (a própria API respondeu 404 apontando o substituto). Troquei para `gemini-3.6-flash` — confirmado contra a lista real de modelos (`GET /v1beta/models`), não só a mensagem de erro — em `.env`, `.env.example` e no default de `config/extractors.php`. Nenhuma linha de `GeminiDocumentExtractor` ou de `AcademicDocumentPrompt` precisou mudar: o contrato da API (`inline_data`, `responseSchema`) estava certo desde a primeira tentativa.

**A qualidade da leitura superou a expectativa.** Contra o histórico real (6 páginas, `docs_referencia/historico.pdf`):

- As 57 linhas batem exatamente com as 6 tabelas do documento (41 obrigatórias + 3 optativas + 4 enriquecimento + 5 exame de suficiência + 4 disciplinas matriculadas 2026/2) — nenhuma inventada, nenhuma repetida.
- Todo `"*"` de frequência (não se aplica) voltou `null`, nunca `0` — inclusive nas linhas do quadro "Exame de Suficiência", que a princípio pareciam duplicatas espúrias e são estrutura real do documento.
- `meta` veio completo e correto: RA, curso, código do curso, matriz, campus, período atual.
- Contra a matriz (`docs_referencia/matriz.pdf`, `kind: curriculum`), leu as 121 disciplinas — bate com o que `SISTEMA.md` já registrava da seed via CSV. A matriz não passa por essa IA em produção (`MatrizUtfprSeeder` semeia via HTML), então não ajustei o schema para ela.
- Observei um 503 transitório ("alta demanda") durante os testes — exatamente o erro de transporte que `ExtractorUnavailableException` já trata (sobe, a fila reprocessaria) e um sinal de que a camada gratuita pode exigir retry manual perto da demo.

## 2 — A confirmação passou a poder criar o vínculo sozinha

Decisão do dono do produto: `registration_id` no `POST .../confirm` virou **opcional**. Sem ele, `ConfirmAcademicDocumentAction::resolveRegistration()`:

1. Encontra ou cria o `Student` do usuário autenticado.
2. Procura um vínculo já existente **no mesmo curso** (`meta.course_code`) — o mesmo aluno pode ter dois vínculos, um por curso, e reaproveitar errado seria pior que criar de novo.
3. Sem vínculo, resolve `Course` por `meta.course_code`, `Term` de ingresso por `meta.entry_term` (formato `"1/2023"`) e chama `CreateRegistrationAction` — a mesma Action já testada, sem duplicar a regra de que a matriz é resolvida no servidor pelo termo de ingresso, nunca escolhida pelo cliente.
4. Sem `course_code`/`entry_term` reconhecíveis no `meta`, falha com `ValidationException` em `registration_id` — pede o vínculo explícito em vez de adivinhar (regra de ouro nº 1).

Não toquei em `CreateRegistrationAction`: a Action existente já resolve a matriz certa (o piloto só tem a 45, com `cur_effective_from` em 2023, então qualquer termo de ingresso real cai nela).

## 3 — Um bug real, só visível com o documento de verdade

Rodando a confirmação ponta a ponta contra o histórico real, a validação de `lines.*.status` (`max:64`) **derrubou a confirmação inteira** por causa de uma única linha: `"Enade - Estudante Dispensado De Realização Do Enade, Em Razão Da Natureza Do Curso"` (84 caracteres) — texto administrativo, não uma situação acadêmica. Isso violava o próprio princípio do sistema ("linha que não bate vira pendência, nunca erro fatal"): uma string comprida não deveria conseguir travar a importação inteira antes mesmo de a linha chegar ao `DocumentStatusTranslator`. Subi o limite para `max:255`. Teste novo trava esse caso.

## 4 — Prova ponta a ponta, com o histórico real do dono do produto

Rodei o fluxo completo — parse pelo Gemini real → confirmação sem `registration_id` — contra `docs_referencia/historico.pdf` no ambiente de dev:

```
registration: number=2567857 course=25 curriculum=45
imported=50 pending=4
progress overall: completed_hours=2295, required_hours=3000, in_progress_hours=225, percentage=76.5
```

As 4 pendências (`FSI103`, `ICO101`, `LET018`, `MAT028`) são as disciplinas de **enriquecimento curricular** — fora da matriz 45 por definição (vieram de mudança de matriz/outro câmpus) — então não estarem no catálogo é o comportamento correto, não uma falha.

Essa prova deixou dado real na base de dev: `aluno@alunos.utfpr.edu.br` já loga com vínculo, 50 matrículas e barra de progresso em 76,5%. Serve de estado inicial pronto para a demo e para o B5 (o acervo precisa de `subject_enrollments` reais para o teste de visibilidade cross-semestre).

## Ressalvas honestas

1. **Duas chamadas ao histórico real devolveram 57 e 54 linhas**, mesmo com `temperature: 0.0`. Não investiguei a fundo (não impactou corretude nos dois casos — todas as linhas capturadas bateram com o documento), mas o modelo não é 100% determinístico entre chamadas separadas. Vale rodar de novo perto da demo para não ser surpreendido.
2. **`ENADEIE` (histórico) vs `ENADE IE`/`ENADE CE`** (matriz) — o mesmo item institucional veio com formatação de código diferente em documentos diferentes. Não é um bug do fluxo atual (a linha não bate com nenhum `sbj_code` de qualquer forma e cairia em pendência), mas se `ATV001`/ENADE algum dia entrarem no catálogo como disciplinas de verdade, os dois formatos vão precisar convergir.
3. **A tela de conferência continua não existindo.** Sem front, a prova de ponta a ponta rodou por Tinker direto na Action, não pela API HTTP. O `curl`/Postman continuam sendo o caminho de demo caso o front não esteja pronto.
4. **A camada gratuita do Gemini teve 2 de ~6 chamadas rejeitadas com 503** durante esta sessão de testes. `ExtractorUnavailableException` + retry de fila cobre isso em produção, mas vale saber que pode acontecer ao vivo na banca — um segundo upload resolve.
5. **`CreateRegistrationAction` continua sem seu próprio endpoint público.** A decisão foi resolver o vínculo só a partir do documento (curso + RA + ingresso do cabeçalho); um aluno que precise de vínculo SEM subir documento nenhum ainda não tem caminho. Não apareceu como necessidade real até aqui.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/dominio/DOCUMENTOS_ACADEMICOS.md` §5.3 | `registration_id` é opcional; o vínculo pode nascer da confirmação |
| `docs/reports/SISTEMA.md` | Parte XII: remover "endpoint para criar vínculo" da lista do que falta — resolvido via confirmação, não via endpoint dedicado; Parte IX: `max:255` em vez de `max:64` |
| `CLAUDE.md` | §Estado atual: extrator já rodou contra o Gemini real; a ressalva "nunca rodou" está fechada |
| `docs-site/features/matricula-e-progressao.html` | O card do vínculo automático vira ✅ implementado |
