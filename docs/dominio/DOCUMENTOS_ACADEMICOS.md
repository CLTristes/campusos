# Documentos acadêmicos — a estrutura real da UTFPR

> **Última atualização:** 2026-09-19 · extraído dos dois documentos reais do
> portal do aluno (`docs_referencia/`, não versionados).
> Este documento **vence o `docs-site/`** onde os dois divergirem: o docs-site foi
> escrito antes de alguém ver um documento de verdade.

O CampusOS não integra com sistema acadêmico nenhum. Todo dado entra por um
**documento que a própria universidade emite** — o que é o que permite o produto
funcionar em qualquer instituição sem negociar acesso. São três entradas:

| Quem | Documento | O que o sistema ganha | Quando |
| --- | --- | --- | --- |
| **Coordenação** | **Consulta Curso e Matriz Curricular** (§4) | O catálogo inteiro: curso, matriz, disciplinas, períodos, carga horária, CHEXT e **os pré-requisitos** | Na implantação, uma vez por matriz |
| **Aluno** | **Histórico escolar** (§1) | Todo o passado dele: o que cursou, a situação de cada disciplina, as horas integralizadas | No primeiro acesso |
| **Aluno** | **Requerimento de matrícula** | O semestre corrente: disciplinas, turma, sala e a grade de horários | Todo semestre, após a matrícula |

A ordem importa e é uma regra: **sem catálogo não há o que importar**. O
histórico do aluno resolve cada linha para uma disciplina do catálogo pelo
código (`sbj_code`); linha sem correspondência vira pendência de conferência,
nunca erro fatal.

---

## 1. O que o histórico escolar contém

Documento de 6 páginas, emitido pelo Departamento de Registros Acadêmicos.

### 1.1 Cabeçalho — repetido em toda página

```
Aluno: 2567857 - FELIPE KURT POHLING       Identidade-UF: 58.193.001-0-SP
Data Nascimento: 06/07/2005   Naturalidade: São Paulo - SP   Nacionalidade: Brasileira
Curso: 25 - Sist. Informação                              Período: 8
Turno: Noite    Matriz: 45 - Sistemas De Informação 02    Situação: Regular
Ingresso: 1/2023
Coeficiente absoluto: 0,8467   Coeficiente normalizado: 0,6351
Data da colação: --/--/----
Câmpus: Francisco Beltrão
```

Daqui sai tudo que identifica o **vínculo**: RA, curso (com código), matriz (com
código), turno, período atual, situação, semestre de ingresso, câmpus.
`Período: 8` **não** é derivado do ingresso — ver §3.

### 1.2 As tabelas de disciplina

Três tabelas com as **mesmas colunas**: *Obrigatórias Cursadas*, *Optativas
Cursadas* e *Enriquecimento Curricular*.

| Coluna | Significado | Vai para |
| --- | --- | --- |
| `Per.Disc/Matriz` | Período **sugerido** da disciplina na matriz | `curriculum_subjects.cbs_term` |
| `Cód.` | Código da disciplina (`ARC102`, `MAT032`) | `subjects.sbj_code` |
| `Disciplina` | Nome | `subjects.sbj_name` |
| `Turma` | `1SI`, `5SI`, `ESPBSI`, `3EAS`, `ESTAGIO`, `INTENSIVA`, `OPTATIVA`, `OPBSI` | `offerings.ofr_class_code` |
| `Tipo` | `R` regular · `F` férias · `E` especial · `D` dispensa · `S` sem horário · `I` intensiva | `subject_enrollments.sen_class_type` |
| `CHS` | Carga horária **semanal** | `subjects.sbj_weekly_hours` |
| `CHT` | Carga horária **total** | `subjects.sbj_hours` |
| `CHEXT` | Carga horária **extensionista** — ver §2 | `subjects.sbj_extension_hours` |
| `Média` | Nota final, escala **0,0–10,0** | `subject_enrollments.sen_grade` |
| `Freq.` | Frequência em %, ou `*` quando não se aplica | `subject_enrollments.sen_attendance` |
| `Semestre` | `1` ou `2` | + `Ano` → `terms` |
| `Ano` | Ano letivo | + `Semestre` → `terms` |
| `Situação/Professores` | A situação (§1.3) + o nome do docente | `sen_status` + `ofr_professor_name` |

> **Achado:** `Freq.` vem como `*` em aprovação por exame de suficiência — não
> houve aula para frequentar. Tratar `*` como `null`, nunca como zero: zero
> reprovaria por falta um aluno aprovado.

### 1.3 As situações — o vocabulário real (10 valores, todos observados)

| Texto no documento | `sen_status` | Conta p/ integralização? |
| --- | --- | --- |
| `Aprovado Por Nota/Frequência` | `approved` | sim |
| `Aprovado Por Nota` | `approved` | sim |
| `Aprovado Em Exame De Suficiência` | `approved_exam` | sim |
| `Crédito Consignado` | `credited` | sim |
| `Enade - Estudante Dispensado...` / `Dispensa` | `exempted` | sim |
| `Reprovado Por Nota/Frequência` | `failed_both` | não |
| `Reprovado Por Nota` | `failed_grade` | não |
| `Reprovado Por Frequência` | `failed_absence` | não |
| `Cancelado` | `cancelled` | não |
| `Cursando` | `enrolled` | não (conta como *em andamento*) |

**Por que 10 e não 3.** Cada valor tem consequência diferente no produto:
`credited` vem de **mudança de matriz** (o documento anota
`>> Mudança de Matriz - Cursou Disciplina(s) Equivalente(s)`) e é a prova de que
o congelamento da matriz no vínculo não é teórico — este aluno trocou de matriz
e carregou equivalências. `approved_exam` tem uma tabela própria no documento
(*Exame de Suficiência*), com a nota do exame. Separar reprovação por nota de
reprovação por falta é o que deixa a coordenação distinguir dificuldade de
conteúdo de problema de permanência.

### 1.4 Optativas — existe um "conjunto", não uma lista solta

```
Optativa 941 · Nome do Conjunto: Optativas · Período inicial 5 · Período final 8
CHS 14 · CH Obrigatória 210 · CH Cursada e Aprovada 90 · CH Faltante 120
```

A matriz define **conjuntos de optativas**, cada um com uma carga horária
própria a cumprir e uma janela de períodos. No MVP há um conjunto só e ele
colapsa em `curricula.cur_elective_hours`; `elective_groups` fica
**◇ planejado** para quando aparecer matriz com dois conjuntos.

### 1.5 Os quadros que fecham a conta

**Quadro Resumo disciplinas** — a fonte de verdade da integralização:

| CHT | Total do curso | Cursada | Cursada e aprovada | Faltante |
| --- | --- | --- | --- | --- |
| Obrigatórias | 2.730 | 2.400 | 2.325 | 405 |
| Optativas | 210 | 90 | 0 | 210 |
| **Soma das disciplinas** | **2.940** | 2.490 | 2.325 | 615 |

> ⚠️ **2.940 NÃO é o total para formar.** Esse quadro soma só disciplinas. O total
> real é **3.000 h** e sai de uma fórmula que só o documento da matriz traz — ver §4.3.

**Quadro Resumo Atividades Extensionistas** — ver §2.

Outras seções aproveitáveis: *Disciplinas Obrigatórias Faltantes* (com o período
de cada), *Dependências*, *Disciplinas Matriculadas* do semestre corrente,
*Exame de Suficiência* e *Histórico de Bloqueios*.

> **Conferência barata e valiosa:** depois de importar, o
> `ProgressReadModel` tem de reproduzir **exatamente** os números do Quadro
> Resumo. Divergiu, a importação está errada — é o melhor teste de aceitação
> que existe para o B4, e sai de graça porque o documento traz o gabarito.

---

## 2. CHEXT — a dimensão que eu tinha modelado errado

Carga horária extensionista **não é uma faixa de horas separada**. Ela é uma
**parcela de dentro** da carga de certas disciplinas:

```
PEX304 Práticas Extensionistas 1   CHT 30   CHEXT 30
PEX405 Práticas Extensionistas 2   CHT 60   CHEXT 60
HSA003 Gestão Econômica            CHT 45   CHEXT 45
```

A prova aritmética: CHT geral das disciplinas 2.940 e CHEXT geral 300. Se fossem
somáveis, o total seria 3.240 (e o documento da matriz **imprime esse 3.240** como
`SOMACH`, justamente para depois descartá-lo). Um está **contido** no outro — mas
não inteiramente, e é aí que mora a sutileza que eu tinha errado: ver §4.3.

| Exigência extensionista | CHEXT | Cursada | Faltante |
| --- | --- | --- | --- |
| Disciplinas obrigatórias | 240 | 180 | 60 |
| Componentes Curriculares Extensionistas (CCE) obrigatórios | 60 | 0 | 60 |
| **Geral do curso** | **300** | **180** | **120** |

**CCE** é a segunda via de cumprir extensão: um projeto de extensão registrado,
fora de disciplina. No caso real: *"Projetos de Extensão em Sistemas de
Informação"*, 60 h obrigatórias, situação *Em andamento*.

**Consequência no modelo:** `subjects.sbj_extension_hours` (a parcela CHEXT) e
uma exigência `curricula.cur_extension_hours` que é **ortogonal** ao total —
verificada em paralelo, nunca somada a ele.

---

## 3. O período do aluno é calculado, não contado

A seção *Detalhes Para o Cálculo do Período do Aluno* traz a regra literal:

> "O aluno que atingir **16 aulas ou mais de carga horária semanal faltante**,
> somando-se a carga horária semanal das disciplinas obrigatórias mais as
> optativas, **não avança de período**."

O documento monta um quadro por período com CHS prevista × CHS cursada e um
saldo acumulado. No caso real o saldo chegou a **−21,00 CHS**, e a conclusão
impressa é: *"Como o aluno atingiu 21,00 aulas de carga horária semanal faltante
ele permanecerá no 8º Período"*.

**Consequência:** `registrations.reg_current_term` é **derivado** de um déficit
de CHS acumulado, não de `entry_term + semestres decorridos`. Por isso
`subjects.sbj_weekly_hours` (CHS) é coluna obrigatória, e não um detalhe: sem
ela o sistema não consegue dizer em que período o aluno está — e dizer errado é
pior que não dizer.

Limite correspondente na matriz: `cur_max_weekly_deficit` (16 na UTFPR).

---

## 4. O documento da matriz — "Consulta Curso e Matriz Curricular"

Seis páginas, emitidas pelo portal. **É o documento mais importante dos três**:
dele sai o catálogo inteiro, e ele é o único que traz **pré-requisito**.

Cabeçalho: `Câmpus: Francisco Beltrão` · `Curso(s): Sist. Informação (25)` ·
`Matriz: 45 - Sistemas De Informação 02` · `Matriz Curricular - Versão 2`.

### 4.1 As colunas

| Coluna | Vai para |
| --- | --- |
| `Período` | `curriculum_subjects.cbs_term` |
| `[OPT]` | código do conjunto de optativas (`941`) — vazio ⇒ obrigatória |
| `Código` · `Disciplina` | `subjects.sbj_code` · `sbj_name` |
| `Modelo de disciplina` | `subjects.sbj_model` — classificação do PPC (§4.2) |
| `Aulas teóricas` + `práticas semanais` | detalhe → `sbj_workload_breakdown` (json) |
| **`Total de aulas semanais`** | **`subjects.sbj_weekly_hours` (CHS)** |
| `APS` · `APCC` · `AD` · `CHEAD` | detalhe → `sbj_workload_breakdown` (zerados nesta matriz) |
| **`Total de horas de CHEXT`** | **`subjects.sbj_extension_hours`** |
| **`Carga horária total`** | **`subjects.sbj_hours` (CHT)** |
| **`Pré-requisito(s)`** | **`prerequisites`** — ver §4.4 |
| `Equivalência(s)` | ⚠️ **truncada na margem direita do PDF** — ver §6 |

### 4.2 `Modelo de disciplina` — 8 valores observados

`FORMAÇÃO BÁSICA E CIENTÍFICA` · `FORMAÇÃO PROFISSIONAL` · `HUMANIDADES - NOVA` ·
`ESTÁGIO` · `ATIVIDADES COMPLEMENTARES` · `TRABALHO DE CONCLUSÃO` ·
`ENADE CONCLUINTE` · `ENADE INGRESSANTE`.

Confirma o que o histórico já sugeria: **estágio, TCC, atividades complementares e
até o ENADE são linhas da matriz**, não faixas de hora paralelas. `ATV001`
(Atividades Complementares) tem 90 h e modelo próprio; `EST501` (Estágio) tem
450 h; `TCC804` tem 120 h; as duas linhas de ENADE têm **0 h**.

### 4.3 O total do curso é 3.000 h — e o documento imprime a fórmula

O rodapé traz o bloco de fechamento da matriz, literal:

```
CHTOBRIGATORIASMATRIZ: 2730      CHEXT_DISCOBRIGATORIAS: 240
CHTOPTATIVASMATRIZ:     210      CHEXT_DISCOPTATIVAS:    315
CHEXTENSAO:             300      TEMA_OBRIGARORIA:        60
CHELETIVA:                0      SOMA_EXT_DISC_TEMA:     615
SOMACH:                3240      TIPOCHTOTALPPC:           3
SOMACHSEMEXT:          2940      CHTOTALPPC:            3000

{CHTOTALPPC := SomaCHSemExt + (CHEXTENSAO - chext_discObrigatorias)}
{CHTOTALPPC := 2940 + (300 - 240)}
{CHTOTALPPC := 3000}
```

**Lendo em português:** as disciplinas somam 2.940 h. Dentro dessas 2.940 já
existem **240 h de extensão** embutidas (HSA003 45 + HSA005 45 + PEX304 30 +
PEX405 60 + HCH023 60). Mas o curso exige **300 h** de extensão. As **60 h que
faltam não cabem em disciplina nenhuma** — têm de vir de um **Componente
Curricular Extensionista (CCE)** autônomo, que no caso real é *"Projetos de
Extensão em Sistemas de Informação"*. Logo o total a integralizar é
**2.940 + 60 = 3.000 h**.

> **Eu tinha errado dos dois lados.** Primeiro modelei extensão como faixa
> somável (daria 3.240 — que o documento calcula e **descarta**). Depois corrigi
> para "totalmente ortogonal, não soma" (daria 2.940). A verdade é o meio: a
> extensão é ortogonal **até onde cabe dentro das disciplinas**, e o que sobra
> vira exigência autônoma que **soma**. `SOMACH` e `SOMACHSEMEXT` existem no
> documento exatamente porque os dois extremos estão errados.

**Consequência no modelo — `curricula`:**

| Coluna | Valor | Origem |
| --- | --- | --- |
| `cur_mandatory_hours` | 2730 | `CHTOBRIGATORIASMATRIZ` |
| `cur_elective_hours` | 210 | `CHTOPTATIVASMATRIZ` |
| `cur_extension_hours` | 300 | `CHEXTENSAO` — exigência total, ortogonal |
| `cur_standalone_extension_hours` | 60 | `TEMA_OBRIGARORIA` — a parcela que **soma** |
| `cur_max_weekly_deficit` | 16 | regra do §3 |
| `cur_max_term_hours` | 390 | requerimento, §5.1 |

`cur_total_hours` **continua não existindo como coluna**: é
`mandatory + elective + standalone_extension`. O princípio se manteve; só a
fórmula ficou mais interessante do que eu supunha.

**A barra de progresso, então, tem TRÊS faixas** (não cinco, não duas):

| Faixa | Felipe hoje |
| --- | --- |
| Obrigatórias | 2.325 / 2.730 h |
| Optativas | 90 / 210 h |
| Extensão autônoma (CCE) | 0 / 60 h |
| **Total** | **2.415 / 3.000 h — 80,5 %** |

Mais o indicador **ortogonal** de extensão: 180 / 300 h (180 vindas de
disciplinas já aprovadas; faltam 60 de `HCH023`, que ele cursa agora, e 60 do CCE).

> **Validação cruzada:** os três documentos fecham entre si na casa da unidade.
> 45+45+30+60 = 180 de CHEXT cursada, batendo com o quadro do histórico; as 60
> restantes são exatamente `HCH023`, que aparece em *Disciplinas Obrigatórias
> Faltantes* **e** em *Disciplinas Matriculadas 2026/2*. Quando a importação
> reproduzir esses números, ela está certa.

### 4.4 Pré-requisitos — o furo fechado

A coluna existe e é legível. O grafo completo das obrigatórias:

| Disciplina | Exige |
| --- | --- |
| `BDD301` Banco de Dados | `MAT029` |
| `MOS302` Modelagem de Software | `REQ203` |
| `POO303` Prog. Orientada a Objetos | `LIP201` |
| `CVM401` Construção/Validação/Manutenção | `MOS302` |
| `EDD404` Estrutura de Dados | `LIP201` |
| `PEX405` Práticas Extensionistas 2 | `PEX304` |
| `ARS502` Arquitetura de Software | `CVM401` |
| `EST501` Estágio Curricular Obrigatório | **`Período: 5`** |
| `WBE501` Desenv. Web Back-End | `POO303` **e** `WFE402` |
| `ISI604` Infraestrutura para SI | `RED202` |
| `MAT032` Tópicos de Pesquisa Operacional | `MAT034` |
| `PIN603` Projeto Integrador CT&I 2 | `PIN503` |
| `SDU601` Sistemas Distribuídos e Ubíquos | `LIP201` **e** `RED202` |
| `INC702` Inteligência Computacional | `LIP201` |
| `TCC704` Trabalho de Conclusão 1 | `MEP602` |
| `MID801` Mineração de Dados | `BDD301` |
| `TCC804` Trabalho de Conclusão 2 | `TCC704` |

Optativas com pré-requisito: `API003` ← `LIP201`+`RED202` · `API004` ← `ARS502` ·
`API005` ← `POO303` · `MAT033` ← `EST003`+`MAT032` · `NEO001` ← `LIP201` ·
`NEO002` ← `BDD301`+`HSA005` · `NEO004` ← `LIP201` · e a cadeia de inglês
`HLA004 → HLA005 → HLA006 → HLA007 → HLA008 → HLA009`.

**Dois tipos, não um.** Além do pré-requisito por disciplina, `EST501` usa
`Período: 5` — **período mínimo**, não carga horária mínima. Então:

```php
enum PrerequisiteType: string {
    case Subject     = 'subject';       // exige outra disciplina cumprida
    case MinimumTerm = 'minimum_term';  // exige estar no Nº período (EST501)
    case Corequisite = 'corequisite';   // ◇ não observado nesta matriz
    case MinimumHours = 'minimum_hours';// ◇ não observado nesta matriz
}
```

`prerequisites.required_cbs_id` é nullable e `prq_min_term` entra como coluna —
`MinimumTerm` não aponta para disciplina nenhuma.

> **A cadeia mais longa da matriz** — `LIP201 → POO303 → WBE501` e
> `MEP602 → TCC704 → TCC804` — é o que faz a simulação de reprovação (Fase 6)
> ter o que mostrar. Reprovar em `LIP201` no 2º período trava `POO303`, `EDD404`,
> `SDU601`, `INC702`, `NEO001`, `NEO004` e, em cascata, `WBE501`.

### 4.5 O conjunto de optativas, completo

```
[941] Optativas · Período inicial/final: 05/08 · Carga horária: 210 · CH semanal: 14
Distribuição por período: 05→04  06→04  07→04  08→04
```

A CHS semanal do conjunto (14) é dividida pelos 4 períodos em que ele se
distribui — **3,5 CHS por período**, e o histórico confirma esse número não
inteiro no quadro de cálculo do período. Vira a tabela `elective_groups`
(`elg_code` 941, `elg_required_hours` 210, `elg_weekly_hours` 14,
`elg_first_term` 5, `elg_last_term` 8) com
`curriculum_subjects.elective_group_elg_id` nullable apontando para ela.
São ~90 optativas no conjunto — de `Cálculo 1` a `Canto Coral` e
`Instrumento musical - Violino 3`.

---

## 5. O requerimento de matrícula — de onde saem as ofertas

Documento de 1 página, emitido na matrícula de cada semestre.

### 5.1 Disciplinas requeridas

| Câmpus | Disciplina | Nome | Turma | Enquadramento | CHS | CHT | Vaga |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Francisco Beltrão | ARS502 | Arquitetura De Software | 5SI | Presencial | 2 | 30 | Não Garantida |
| Francisco Beltrão | EST003 | Probabilidade E Estatística | 5SI | Presencial | 4 | 60 | Não Garantida |

Mais três linhas de rodapé com regra de negócio:

```
Total de disciplinas: 5
Carga horária total utilizada: 240 [CHS: 16]
Carga horária máxima permitida: 390
```

→ `curricula.cur_max_term_hours` (390). O sistema passa a poder avisar
*"você está a 150 h do teto do semestre"* antes de o portal recusar a matrícula.

### 5.2 A grade de horários — o calendário que se monta sozinho

Uma matriz de horário × dia da semana, com células no formato
`DISCIPLINA-TURMA/SALA`:

```
         Segunda            Terça              Quarta             Quinta
N2  19h30 HLA001-5SI/Q103   EST003-5SI/Q208    EST003-5SI/Q209    WBE501-5SI/Q208
N3  20h20 HLA001-5SI/Q103   EST003-5SI/Q208    EST003-5SI/Q209    WBE501-5SI/Q208
N4  21h20 ARS502-5SI/Q208   WBE501-5SI/Q209    PIN503-5SI/Q208    HLA001-5SI/Q105
N5  22h10 ARS502-5SI/Q208   WBE501-5SI/Q209    PIN503-5SI/Q208    HLA001-5SI/Q105
```

Os códigos de horário são `M1..M6` (manhã), `T1..T6` (tarde), `N1..N5` (noite),
cada um com início e término impressos no próprio documento.

→ `offerings.ofr_schedule` como json de `{dia, inicio, fim, sala}`, agrupando
slots contíguos da mesma disciplina. É daqui que sai a geração automática dos
eventos de aula (`events`, ◎ esticada) — e é o que faz o calendário do aluno
aparecer preenchido no instante em que ele confirma a importação.

---

## 6. ⚠️ Pendências do dono do produto

### ✅ Fechadas pelo documento da matriz (19/09, 02:35)

1. ~~**O histórico não traz pré-requisito nenhum.**~~ **Resolvido.** O documento
   da matriz traz a coluna `Pré-requisito(s)` completa — §4.4. As Fases 5 e 6 do
   card de matrícula (o que libera, o que trava, simulação de reprovação) saem do
   papel.
2. ~~**O histórico de um aluno ≠ a matriz do curso.**~~ **Resolvido, e melhor do
   que se pedia.** A matriz tem documento próprio, emitido por curso e não por
   aluno — é exatamente o que a coordenação sobe na implantação. O histórico volta
   a ter uma função só: o passado de UM aluno. **O atalho de hackathon deixou de
   ser necessário, e isso muda o pitch**: o fluxo demonstrado é o real.

### Abertas

3. ⚠️ **A coluna `Equivalência(s)` está truncada no PDF.** O documento é mais
   largo que a página e a última coluna sai cortada — dá para ver `AC3…`, `IS3…`,
   `LP3…`, `BD3…`, mas não o código inteiro. É ela que explica o
   `Crédito Consignado` do histórico (equivalência por mudança de matriz).
   **Impacto:** a importação funciona sem ela; o que não funciona é resolver
   automaticamente uma disciplina cursada na matriz antiga. *Precisa de um
   re-export em paisagem, ou da mesma consulta em HTML.* **Não bloqueia o B2.**
4. **Atividades complementares são uma DISCIPLINA** (`ATV001`, 90 h, modelo
   `ATIVIDADES COMPLEMENTARES`), confirmado pela matriz. Decidir: a tabela
   `complementary_activities` continua como **acervo de evidência** que justifica
   a aprovação de `ATV001` (com o contador por categoria), ou o produto trata tudo
   como disciplina e abre mão do contador? *Palpite: manter — o contador por
   categoria é a feature; `ATV001` é só como a universidade registra o desfecho.*
   **Ainda falta a resolução do curso com os tetos por categoria.**
5. **Frequência mínima não aparece em documento nenhum.** A escala de nota está
   confirmada (0,0–10,0), mas `MAT032` reprovou com 47,1 % e `NEO001` **aprovou**
   com 52,9 % — então não é um corte simples em 75 %. Precisa da resolução para
   cravar, ou o sistema simplesmente **repete a situação impressa** no documento e
   não recalcula nada (o que é mais seguro e é o que o MVP faz).
6. **Uma matriz, um câmpus.** Este documento cobre Francisco Beltrão / matriz 45.
   O produto suporta N por construção, mas a demo terá uma só — e vale dizer isso
   na banca em vez de deixar parecer que há mais.
