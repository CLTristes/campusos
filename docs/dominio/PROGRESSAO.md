# Progressão da graduação — as regras

> **Área:** `journey` (fatos do aluno) lendo `catalog` (dados mestres).
> **Última atualização:** 2026-09-19 · v0.2.0
> Cobre o desafio **5.1** do HackLab. Os documentos que alimentam estas regras
> estão descritos em [`DOCUMENTOS_ACADEMICOS.md`](DOCUMENTOS_ACADEMICOS.md).

## O problema

O estudante não consegue enxergar a própria trajetória: quanto falta, o que
trava, quando termina. A informação existe — está espalhada entre o histórico
escolar, a matriz curricular e a resolução de atividades complementares — mas
ninguém cruza os três. O resultado é a descoberta no último semestre de que
faltam 60 horas de uma categoria que já tinha estourado o teto dois anos antes.

## As regras

### 1. Vínculo, não "aluno pertence a curso"

Um aluno tem um **vínculo** (`registrations`) com um curso, sob uma matriz
específica, a partir de um semestre de ingresso. Um mesmo aluno pode ter mais
de um vínculo (mudou de curso, segunda graduação), e **cada vínculo tem a sua
matriz**.

### 2. A matriz do vínculo é congelada no ingresso

`registrations.curriculum_cur_id` é uma FOTO da matriz vigente quando o aluno
ingressou, e **nunca** se atualiza quando o colegiado publica matriz nova. Sem
esse congelamento, a barra de progresso de toda a instituição mudaria de valor
no dia de uma reforma curricular, sem ninguém ter feito nada.

A matriz é resolvida **no servidor** — a mais recente cujo `cur_effective_from`
não é posterior ao termo de ingresso. Nunca vem do cliente: deixar o cliente
escolher a matriz é deixá-lo escolher o próprio critério de formatura.

### 3. Uma matrícula é vínculo × disciplina × semestre

O índice único de `subject_enrollments` é
`(registration_reg_id, subject_sbj_id, term_trm_id)`. Cursar a mesma disciplina
**em outro semestre** é permitido e gera uma linha nova — é exatamente assim que
uma reprovação aparece no histórico (duas linhas da mesma disciplina), e é daí
que a contagem de tentativas sai de graça.

### 4. Dez situações, e o que cada uma faz com o progresso

Vocabulário observado no histórico real (§1.3 do `DOCUMENTOS_ACADEMICOS.md`):

| Situação | Conta para integralização? | Trava pré-requisito? |
| --- | --- | --- |
| `enrolled` — cursando | não (conta como **em andamento**) | sim (ainda não cumpriu) |
| `approved` — aprovado por nota/frequência | **sim** | não |
| `approved_exam` — aprovado em exame de suficiência | **sim** | não |
| `credited` — crédito consignado (mudança de matriz) | **sim** | não |
| `exempted` — dispensa / ENADE | **sim** | não |
| `failed_grade` — reprovado por nota | não | sim |
| `failed_absence` — reprovado por frequência | não | sim |
| `failed_both` — reprovado por nota e frequência | não | sim |
| `withdrawn` — trancado | não | sim |
| `cancelled` — cancelado | não | sim |

A pergunta *"isto conta?"* mora **no enum**, num lugar só. Três cópias levemente
diferentes dela é como um sistema acadêmico passa a mostrar três números
diferentes para a mesma pergunta.

### 5. Aprovação: frequência primeiro, nota depois

Regra informada pelo dono do produto em 19/09/2026:

| Frequência | Exigência de nota | Resultado se não atingir |
| --- | --- | --- |
| **< 50 %** | nenhuma — reprovado independente da nota | `failed_absence` |
| **50 % ≤ f < 75 %** | média **≥ 8,0** | `failed_grade` |
| **≥ 75 %** | média **≥ 6,0** | `failed_grade` |

Escala de nota: **0,0 a 10,0** (confirmada no histórico).

**O sistema NÃO recalcula a situação de uma matrícula importada.** Ele grava a
situação que o documento oficial imprime — **confirmado como decisão do dono do
produto em 19/09/2026**, diante da divergência do `ISI604` (pendência 1).
Discordar do documento oficial seria o sistema contradizer o registro acadêmico
da pessoa. A regra acima serve para outras três
coisas:

1. **Alerta preventivo** — "você está com 62 % de frequência nesta disciplina:
   daqui em diante precisa de média 8,0, não 6,0". É informação que hoje ninguém
   dá ao aluno a tempo.
2. **Simulação** de reprovação (impacto na formatura).
3. **Conferência** da importação — divergência entre o previsto e o impresso é
   sinal de leitura errada do documento, não de regra errada.

> **Descoberta útil:** o próprio rótulo da situação codifica a faixa de
> frequência. Nos 14 aprovados do histórico real, **sem exceção**:
> `Aprovado Por Nota/Frequência` ⟺ frequência ≥ 75 %, e `Aprovado Por Nota`
> ⟺ frequência < 75 %. Isso permite inferir a faixa mesmo quando o campo de
> frequência vem vazio (`*`) no documento.

### 6. Disciplina sem aula não tem frequência

`ESTÁGIO`, `TRABALHO DE CONCLUSÃO`, `ATIVIDADES COMPLEMENTARES` e as duas
linhas de `ENADE` não têm frequência de aula — a regra 5 **não se aplica** a
elas. No histórico real, `EST501` (Estágio) aparece com frequência `0,0` e
situação *Aprovado Por Nota*: zero ali significa "não se aplica", não "faltou a
tudo". Tratar como falta reprovaria todo mundo que fez estágio.

Pelo mesmo motivo, frequência `*` no documento é `null`, nunca zero.

### 7. O total a integralizar são três faixas, e a extensão é ortogonal

```
total = obrigatórias + optativas + extensão autônoma
```

A carga extensionista exigida pelo curso (`cur_extension_hours`, 300 h na
matriz 45) é verificada **em paralelo** e não entra no total: 240 dessas horas
moram *dentro* de disciplinas que o aluno já cursa, e só as 60 restantes
(`cur_standalone_extension_hours`) são uma exigência autônoma que soma.

Detalhe e a fórmula do documento em `DOCUMENTOS_ACADEMICOS.md` §4.3.

### 8. Horas de uma natureza não escorrem para outra

Excedente de optativa não cobre estágio faltando. Cada faixa satura no próprio
teto, e o total é a soma das faixas **saturadas** — senão um aluno com 400 horas
extras de optativa aparece "formado" com o estágio zerado.

### 9. A carga horária integralizada é congelada na aprovação

`subject_enrollments.sen_hours_earned` copia `subjects.sbj_hours` no momento em
que a matrícula vira aprovada. A disciplina pode mudar de carga depois; o que já
foi integralizado não muda junto.

### 10. O período do aluno é calculado por déficit de CHS

Não é `ingresso + semestres decorridos`. A regra impressa no histórico:

> "O aluno que atingir **16 aulas ou mais de carga horária semanal faltante**,
> somando-se a carga horária semanal das disciplinas obrigatórias mais as
> optativas, **não avança de período**."

O limite (16) é dado da matriz: `cur_max_weekly_deficit`.

### 11. A previsão de formatura usa o ritmo REAL

Horas restantes divididas pela média de horas aprovadas por semestre **cursado**,
não pelo ritmo ideal da matriz. O ideal engana; o real é o que o aluno precisa
ouvir.

### 12. Nada de progresso atravessa instituição

Toda leitura passa pelo `EntityScope`. Regra herdada, mas que aqui tem peso
extra: histórico escolar é dado pessoal sensível.

### 13. Pré-requisito tem quatro tipos, cada um checado do seu jeito (B8)

`prerequisites.prq_type` distingue: **`subject`** (a disciplina exigida
precisa estar aprovada), **`corequisite`** (aprovada OU cursando agora, pode
ser simultânea), **`minimum_hours`** (horas totais aprovadas >= um piso —
não observado na matriz 45 real), **`minimum_term`** (o aluno precisa estar
a partir de um período — o caso real é `EST501`, "Período: 5"). `journey`
nunca importa `CampusOs\Catalog\Enums\PrerequisiteType`: o tipo é sempre
comparado pelo `->value` do enum que já veio junto na relação Eloquent, nunca
pela classe (`EligibilityReadModel`).

### 14. A simulação nunca escreve — clona o cenário em memória

"E se eu reprovar em X?" não precisa de Action nem de transação: remove o
código informado do conjunto de disciplinas aprovadas e recalcula, sobre o
MESMO grafo de pré-requisitos, a "primeira oportunidade" (o período mais cedo
possível) de cada disciplina pendente — comparando o cenário real contra o
simulado. Um pré-requisito tipo `subject` empurra um período pra frente
(precisa terminar antes); um `corequisite` não empurra nada (pode ser
simultâneo). É por isso que reprovar uma disciplina no meio de uma cadeia
longa (`LIP201 → POO303 → WBE501` na matriz real) atrasa TODAS as
disciplinas a jusante, não só a reprovada.

## ⚠️ Pendências do dono do produto

1. **`ISI604` contradiz a regra 5 — registrado, não resolvido.** No histórico
   real: média **7,4** com frequência **73,0 %** — faixa que exigiria 8,0 — e o
   documento diz *Aprovado Por Nota*. Hipóteses não verificadas: abono de falta,
   arredondamento do cálculo oficial de frequência, conselho de classe, ou o
   corte de média nessa faixa ser diferente de 8,0.

   **Decisão do dono do produto (19/09/2026):** manter a regra 5 como informada e
   **a importação segue o que o histórico do aluno diz**. Divergir do documento
   oficial seria o sistema discordar do registro acadêmico da pessoa — o que é um
   bug, não uma feature. A regra permanece valendo para o alerta preventivo, a
   simulação e a conferência da leitura.
2. **Tetos por categoria de atividade complementar** — precisa da resolução do
   curso. Sem ela, `complementary_categories` não pode ser semeada e o contador
   por categoria não existe.
3. **`ATV001` é uma disciplina** (90 h, modelo `ATIVIDADES COMPLEMENTARES`) **e**
   pretendemos ter um contador por categoria. Decidir se os dois coexistem
   (`complementary_activities` como acervo de evidência que justifica a aprovação
   de `ATV001`) ou se o produto trata tudo como disciplina.

   **Decisão do dono do produto (19/09/2026):** os dois coexistem, mas **nunca se
   tocam**. `complementary_activities`/`complementary_categories` (B7) são o
   tracker pessoal do aluno — teto por categoria, certificado guardado —
   puramente informativo, e **não somam no `ProgressReadModel`**. `ATV001`,
   quando aparecer no histórico oficial importado, continua sendo o que de fato
   conta como aprovado dentro de "obrigatórias" (2730 h). Motivo: inventar uma
   segunda faixa de progresso a partir do B7 arriscaria contar as mesmas horas
   duas vezes (uma como `ATV001` na importação, outra como faixa nova) sem uma
   regra de conciliação — e o desafio pede o estudante *acompanhar* atividades
   complementares, não uma segunda fonte de verdade para o total de horas.
4. **A regra 10 (déficit de CHS) não está implementada — `EligibilityReadModel`
   usa uma aproximação.** "Período atual" para checar pré-requisitos
   `minimum_term` (B8) é `termsAttended` — o número de semestres reais
   distintos que o aluno já cursou — não o déficit acumulado do documento.

   **Por que a aproximação, e por que ela é honesta:** validei a fórmula
   completa (obrigatórias + optativas) contra o histórico real do dono do
   produto — bate exatamente (déficit final de −21,00 CHS, "permanecerá no
   8º Período"). A PARTE DE OBRIGATÓRIAS reconstrói perfeitamente com o
   schema atual (`curriculum_subjects.cbs_term` × `EnrollmentStatus`). A
   parte de OPTATIVAS não: o documento atribui cada matrícula de eletiva
   aprovada a um "slot" de período da matriz-conjunto (`elective_groups`),
   e nosso schema não modela isso — uma optativa não carrega o período em
   que "contou" para o déficit, só o termo real em que foi cursada. Sem um
   segundo histórico real para desempatar entre hipóteses de atribuição,
   reconstruir essa parte seria adivinhar uma regra não validável. Para
   ESTE aluno (`termsAttended = 8`), a aproximação bate com o "Período: 8"
   impresso — mas isso é coincidência de este aluno não ter tido nenhum
   trancamento nem lacuna entre semestres, não prova de que a aproximação
   sempre bate.
   **Quando revisitar:** se `registrations.reg_current_term` virar coluna de
   verdade (a regra 10 sendo implementada de fato), `EligibilityReadModel`
   deve passar a consultá-la em vez de calcular `termsAttended` sozinho.

## Mapa de código

| Regra | Onde vive | Teste |
| --- | --- | --- |
| 1, 2 | `journey/src/Actions/CreateRegistrationAction.php` | `VinculoTest` |
| 3 | migration `create_subject_enrollments_table` (índice único) | `VinculoTest` |
| 4 | `journey/src/Enums/EnrollmentStatus.php` | `EnrollmentStatusTest` |
| 5, 6 | `journey/src/Support/ApprovalPolicy.php` | `ApprovalPolicyTest` |
| 7, 8, 9 | `journey/src/ReadModels/ProgressReadModel.php` | `ProgressoTest` |
| 10 | **não implementada** — ver ⚠️ pendência 4; `EligibilityReadModel` usa `termsAttended` como aproximação | — |
| 11 | `journey/src/ReadModels/ProgressReadModel.php` | `ProgressoTest` |
| 12 | `core` (`Entityable` + `EntityScope`) | `ProgressoTest` |
| 13 | `journey/src/ReadModels/EligibilityReadModel.php::blockedReasons` | `EligibilidadeESimulacaoTest` |
| 14 | `journey/src/ReadModels/EligibilityReadModel.php::simulate` | `EligibilidadeESimulacaoTest` — "simular reprovação empurra em cascata..." |
