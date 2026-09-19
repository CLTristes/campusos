# 📚 Documentação

> **Comece aqui.** A documentação está dividida em três eixos: **arquitetura**
> (COMO o sistema é construído), **domínio** (O QUE o sistema faz — as regras de
> negócio) e **reports** (relatórios de entrega versionados + o overview
> consolidado + o estado de sincronização de contexto).
>
> Regra-mestra de governança: em divergência entre documento e código, **o
> documento canônico vence** — e a divergência é um bug a corrigir (via
> `/sync-docs` ou correção do doc). Se o projeto adotar uma base externa (Notion,
> wiki), registre aqui que ela é a canônica.

## Mapa de leitura por objetivo

| Você quer... | Leia |
| --- | --- |
| Entender o sistema inteiro de uma vez | [reports/SISTEMA.md](reports/SISTEMA.md) (overview *evergreen*, ponta a ponta) |
| Entender POR QUE a arquitetura é assim | [../README.md](../README.md) (o tutorial do template) |
| Saber as regras de trabalho (IA e humanos) | [../CLAUDE.md](../CLAUDE.md) |
| Entender as regras de negócio | [dominio/PROGRESSAO.md](dominio/PROGRESSAO.md) (as 12 regras da graduação) |
| Entender de onde vêm os dados | [dominio/DOCUMENTOS_ACADEMICOS.md](dominio/DOCUMENTOS_ACADEMICOS.md) (os três documentos do portal) |
| Ver o desenho do produto, inclusive o que não existe | [../docs-site/index.html](../docs-site/index.html) |
| Mexer num módulo sem furar fronteira | [arquitetura/ARQUITETURA.md](arquitetura/ARQUITETURA.md) → [arquitetura/COMUNICACAO.md](arquitetura/COMUNICACAO.md) → [arquitetura/FRONTEIRAS.md](arquitetura/FRONTEIRAS.md) |
| Escrever código novo (FAQ prático) | [arquitetura/IMPLEMENTACAO.md](arquitetura/IMPLEMENTACAO.md) |
| Saber o que foi entregue em cada versão | [reports/](reports/README.md) |
| Saber o que ainda não foi sincronizado | [reports/sync-state.json](reports/sync-state.json) |

## 🏗️ `arquitetura/` — como o sistema é construído

| Documento | O que cobre |
| --- | --- |
| [ARQUITETURA.md](arquitetura/ARQUITETURA.md) | o paradigma modular, fronteiras, por que isso importa com IA, mapa de módulos, multi-tenant, o fluxo canônico, plano de nascimento |
| [MODULOS.md](arquitetura/MODULOS.md) | o pacote `internachi/modular`, instalação e as árvores de arquivos de cada módulo |
| [CORE.md](arquitetura/CORE.md) | o shared kernel: contratos, eventos, DTOs, Entityable/EntityScope/TenantContext, auditoria |
| [COMUNICACAO.md](arquitetura/COMUNICACAO.md) | os 3 mecanismos entre módulos: contratos, eventos, read models/DTOs (+ `config/models.php`) |
| [FRONTEIRAS.md](arquitetura/FRONTEIRAS.md) | o ArchTest: a fronteira como regra executável que quebra o CI |
| [IMPLEMENTACAO.md](arquitetura/IMPLEMENTACAO.md) | FAQ de código: jobs/filas resilientes, Strategy/resolver, Actions, canais, Resource vs DTO, idempotência |
| [BANCO.md](arquitetura/BANCO.md) | convenções (prefixo de 3 letras, UUID, SoftDeletes), ownership por módulo, ER |
| [CODE_STYLE.md](arquitetura/CODE_STYLE.md) | `strict_types`, Pint, nomenclatura, namespaces de módulo, segurança, commits |

## 🧾 `dominio/` — o que o sistema faz (regras de negócio)

É a fonte que a skill `/dominio` carrega para responder qualquer pergunta de
negócio. Contrato de cada documento em [dominio/README.md](dominio/README.md).

| Documento | O que cobre |
| --- | --- |
| [DOCUMENTOS_ACADEMICOS.md](dominio/DOCUMENTOS_ACADEMICOS.md) | **De onde vêm os dados.** A estrutura real dos três documentos do portal da UTFPR (histórico escolar, matriz curricular, requerimento de matrícula): colunas, vocabulário de situação, quadros de resumo, a fórmula das cargas horárias e o grafo de pré-requisitos |
| [PROGRESSAO.md](dominio/PROGRESSAO.md) | **As 12 regras da graduação.** Vínculo, matriz congelada, as dez situações de matrícula, a regra de aprovação (frequência × nota), as três faixas de carga horária, a previsão de formatura |
| [ACESSO.md](dominio/ACESSO.md) | **Quem entra, no quê.** Tenant = instituição, 4 papéis, e-mail único por instituição, cadastro livre do aluno pelo domínio do e-mail, verificação por código |
| [ACERVO.md](dominio/ACERVO.md) | **O acervo do veterano (5.2).** A escada de 5 visibilidades, a regra que faz a nota atravessar semestres, o que não entrou ainda (curadoria por voto) |
| [TAREFAS.md](dominio/TAREFAS.md) | **Tarefas da turma (B6, 5.2).** Tarefa não nasce privada, adotar copia (nunca compartilha a linha), a agenda filtra por termo corrente — ao contrário do acervo de notas |

> O **desenho de produto** — inclusive das features que ainda não existem — vive
> em [`docs-site/`](../docs-site/index.html) e é canônico para elas, no mesmo
> espírito dos cards `◇ planejado` do FibroMais.

## 📦 `reports/` — relatórios de entrega + estado de sincronização

| Documento | O que cobre |
| --- | --- |
| [reports/README.md](reports/README.md) | a dinâmica de reports E o contrato do `sync-state.json` (versionamento de contexto) |
| [reports/SISTEMA.md](reports/SISTEMA.md) | **overview consolidado** *evergreen* — o retrato completo e sempre-atual do sistema |
| [reports/sync-state.json](reports/sync-state.json) | o índice máquina-legível do que já foi propagado para a doc evergreen |
| [reports/v0.0.1/](reports/v0.0.1/) | fundação: o nascimento do template |

---

### Convenções desta documentação

- Documentos de **arquitetura** descrevem estruturas estáveis; documentos de
  **domínio** descrevem regras de negócio e citam as classes que as implementam
  (seção "Mapa de código" ao final de cada um); **reports** são fotografias
  datadas — não se atualizam retroativamente (exceto o `SISTEMA.md`, que é
  *evergreen* e é atualizado pelo `/sync-docs`).
- Trechos marcados `⚠️ PENDENTE` dependem de decisão do dono do produto ou de
  validação externa — mantenha-os visíveis até resolver.
