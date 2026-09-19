# 📦 Documento de Entrega — CampusOS · v0.7.0

## Horas complementares e certificados (B7) — desafio 5.1

> **Data:** 19/09/2026
> **Branch:** `feat/horas-complementares`
> **Escopo:** B7 completo (Fases 1–2 de `docs-site/features/horas-e-certificados.html`) — teto por categoria, cadastro de atividade com certificado, contador com aviso preventivo. Fase 3 (homologação da coordenação) e a leitura automática do certificado por IA ficam para depois, como o próprio desenho já marcava.
> **Qualidade:** 184 testes / 557 asserções verdes · Pint verde · ArchTest verde

## O que mudou, em uma frase

O aluno declara uma atividade complementar com certificado, vê na hora quanto conta e quanto estourou o teto da categoria — sem inventar uma segunda fonte de verdade para o total de horas da graduação.

## 0 — A pendência resolvida antes do código: `ATV001` × `complementary_activities`

`PROGRESSAO.md` e `DOCUMENTOS_ACADEMICOS.md` já registravam, desde antes desta entrega, uma pendência explícita: a matriz real tem `ATV001` — uma disciplina de 90h que já soma dentro de "obrigatórias" quando aprovada pelo histórico — e o B7 cria uma SEGUNDA forma de registrar horas complementares. Perguntei ao dono do produto antes de tocar em `ProgressReadModel`: **as duas fontes nunca se tocam**. `complementary_activities` é o tracker pessoal do aluno, puramente informativo; `ATV001`, quando aparecer no histórico oficial, continua sendo o que conta. Decisão registrada em `PROGRESSAO.md` (pendência 3) e `DOCUMENTOS_ACADEMICOS.md` (pendência 4, agora resolvida).

## 1 — O teto é da matriz, não do aluno

`complementary_categories` pertence ao `catalog` (mesma razão de `prerequisites` viver lá): é regra do colegiado, não fato de aluno. Sem `SoftDeletes` — dado mestre, mesmo padrão de `curriculum_subjects`. Vem embutido em `GET /courses/{course}/curriculum` (chave nova `complementary_categories`, fora de `workload` — puramente informativo), sem precisar de endpoint próprio.

Seed **fictício** (`ComplementaryCategorySeeder`, replicando o exemplo do próprio desenho) — a resolução real de atividades complementares da UTFPR ainda não chegou; pendência já registrada no `docs-site`.

## 2 — O corte é por categoria, nunca por certificado

```php
// ComplementaryHoursReadModel — por categoria: min(SUM(claimed), teto).
// O total é a soma dos MÍNIMOS, nunca a soma bruta declarada.
$granted = min($claimed, $category->ccg_max_hours);
```

Dividir a perda entre linhas é ambíguo quando o teto corta no meio de um conjunto (três certificados de 20h, teto de 50h — quem perde as 10h?) — o desenho já apontava essa armadilha, e o read model nunca tenta resolvê-la: agrega por categoria, ponto. `cac_hours_granted` (coluna por atividade) fica reservada para a homologação MANUAL futura e fica `null` em toda esta entrega.

## 3 — `POST /me/complementary-activities`: a atividade e o resumo já recalculado, numa chamada só

```json
{"data": {"id": "01a0…", "hours_claimed": 20, "status": "submitted"}, "capped": true, "hours_not_counted": 46}
```

O front não precisa de segunda chamada pra saber se estourou o teto. `GET /me/complementary-activities` devolve a lista mais o `summary` por categoria — mesmo formato, reaproveitado.

A categoria é validada contra `registrations.curriculum_cur_id` (a matriz **congelada** do vínculo) — mesma fonte de verdade que o resto da progressão usa; declarar contra o teto de uma matriz alheia é 422.

## 4 — O que existe agora

| Rota | O que faz |
| --- | --- |
| `GET /api/v1/courses/{course}/curriculum` | Ganha `complementary_categories` (o teto de cada categoria da matriz) |
| `GET /api/v1/me/complementary-activities` | Lista as próprias + `summary` por categoria |
| `POST /api/v1/me/complementary-activities` | Declara (com certificado opcional) — `capped`/`hours_not_counted` na resposta |

22 endpoints no total agora, 21 tabelas.

## 5 — O certificado fica fora da trilha de auditoria

`ComplementaryActivityObserver` esconde `cac_certificate_path` do diff — mesmo padrão de `erq_file_path` em `enrollment_requests`: o que se audita é a decisão sobre as horas, não onde o byte está guardado.

## Ressalvas honestas

1. **Tetos fictícios.** `ComplementaryCategorySeeder` replica o exemplo do desenho (eventos 40h, monitoria 80h, extensão 80h, iniciação científica 100h, estágio não obrigatório 60h, cursos 60h) — não é a resolução real da UTFPR. Pendência já registrada, e o dono do produto está ciente.
2. **Sem fila de homologação.** Qualquer valor declarado conta (`draft`/`submitted` ambos, só `rejected` não) — não existe endpoint pra coordenação aprovar/rejeitar. O enum `ActivityStatus::countsTowardHours()` já é o ponto único de troca para quando isso entrar.
3. **Sem leitura automática do certificado por IA.** O contrato `AcademicDocumentExtractor` serviria aos dois casos, mas B7 não conectou os dois — certificado fica só guardado, sem OCR.
4. **`complementary_activities` nunca soma no `ProgressReadModel`** — decisão deliberada (ver seção 0), não uma omissão.
5. **Sem tela**, a prova roda por Pest (9 testes em `journey` + 1 em `catalog`) e por `curl`/Postman.

## Impacto na documentação (para o `/sync-docs`)

| Alvo | O que precisa absorver |
| --- | --- |
| `docs/reports/SISTEMA.md` | `catalog` ganha `complementary_categories`; `journey` ganha `complementary_activities`; 22 endpoints (Parte X); 21 tabelas (Parte V); 184/557 (Parte IX); Parte XII perde a linha de horas complementares em `journey` |
| `CLAUDE.md` | Mapa de módulos: `journey` — B7 fechado; §Estado atual |
| `docs/arquitetura/BANCO.md` | Ownership de `complementary_categories` e `complementary_activities` |
| `docs-site/features/horas-e-certificados.html` | Fases 1–2 viram ✅ implementado |
| `docs-site/plano-de-ataque.html` | B7 vira ✅ feito |
| `docs-site/banco-de-dados.html` | Cards `complementary_categories`/`complementary_activities` viram ✅ em código |

Já feito nesta entrega (barato, não esperou o `/sync-docs`): `docs/dominio/HORAS_COMPLEMENTARES.md` criado; `docs/README.md`, `PROGRESSAO.md` (pendência 3) e `DOCUMENTOS_ACADEMICOS.md` (pendência 4) atualizados com a decisão do dono do produto.
