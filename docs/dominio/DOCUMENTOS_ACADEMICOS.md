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
| **Coordenação** | Documento da matriz (ver §4) | O catálogo: curso, matriz, disciplinas, períodos, carga horária | Na implantação, uma vez por matriz |
| **Aluno** | **Histórico escolar** | Todo o passado dele: o que cursou, a situação de cada disciplina, as horas integralizadas | No primeiro acesso |
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
| **Geral** | **2.940** | 2.490 | 2.325 | 615 |

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

A prova aritmética: CHT geral do curso 2.940 e CHEXT geral 300. Se fossem
somáveis, o total seria 3.240 — e o documento traz os dois em quadros separados,
justamente porque um está **contido** no outro.

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

## 4. O requerimento de matrícula — de onde saem as ofertas

Documento de 1 página, emitido na matrícula de cada semestre.

### 4.1 Disciplinas requeridas

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

### 4.2 A grade de horários — o calendário que se monta sozinho

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

## 5. ⚠️ Pendências do dono do produto

1. **O histórico não traz pré-requisito nenhum.** Ele lista disciplinas e
   períodos, mas não o grafo de dependências — e as Fases 5 e 6 do
   [card de matrícula e progressão](../../docs-site/features/matricula-e-progressao.html)
   (o que libera, o que trava, a simulação de reprovação) **dependem dele**.
   Precisa de um segundo documento do portal (a matriz/grade curricular com
   pré-requisitos) ou de cadastro manual pela coordenação. **Enquanto não
   houver, `prerequisites` nasce vazia e as duas features não funcionam.**
2. **O histórico de um aluno ≠ a matriz do curso.** Funciona como fonte da
   matriz *neste caso específico* porque o Felipe está no último período e
   cursou tudo. Para um aluno de 2º período o documento cobriria uma fração da
   grade. O caminho certo é a coordenação subir o documento da matriz; usar o
   histórico é o atalho de hackathon — **e precisa ser dito assim na
   apresentação.**
3. **Atividades complementares são uma DISCIPLINA** (`ATV001`), não uma faixa de
   horas: aparece em *Disciplinas Obrigatórias Faltantes*, período 8. O mesmo
   vale para estágio (`EST501`, CHT 450, turma `ESTAGIO`) e TCC (`TCC704`,
   `TCC804`). Decidir: a tabela `complementary_activities` continua existindo
   como **acervo de evidência** que justifica a aprovação de `ATV001`, ou o
   produto trata tudo como disciplina e abre mão do contador por categoria?
   *Palpite: manter — o contador por categoria é a feature; `ATV001` é só como a
   universidade registra o desfecho.*
4. **Escala de nota confirmada:** 0,0–10,0 (média 6,0 em RED202 aprovado por
   exame; 0,0 em MAT032 reprovado). **Frequência mínima não aparece explícita** —
   MAT032 tem 47,1% e NEO001 tem 52,9% com aprovação por nota. Precisa da
   resolução para cravar o mínimo.
