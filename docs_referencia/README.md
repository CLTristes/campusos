# Documentos de referência

Esta pasta guarda os **documentos reais do portal do aluno da UTFPR** que servem
de referência para o extrator (`ClaudeDocumentExtractor`) e para os seeds.

**Os PDFs não são versionados** (ver `.gitignore`). Eles contêm nome completo,
data de nascimento, número de identidade, RA, notas e frequências de uma pessoa
real — dado pessoal sensível não entra em histórico de git, de onde não sai mais.

Para trabalhar no extrator, coloque aqui:

| Arquivo | O que é | De onde sai |
| --- | --- | --- |
| `historico.pdf` | Histórico escolar completo — disciplinas cursadas, situação, CHS/CHT/CHEXT, faltantes, resumo de integralização e extensionista | Portal do aluno → Histórico Escolar |
| `requerimento-<ano>-<periodo>.pdf` | Confirmação de disciplinas requeridas — as disciplinas do semestre, turma, sala e a grade de horários | Portal do aluno → Requerimento de matrícula |

A **estrutura** desses documentos (as colunas, os valores possíveis de situação,
os quadros de resumo) está documentada em
[`docs/dominio/DOCUMENTOS_ACADEMICOS.md`](../docs/dominio/DOCUMENTOS_ACADEMICOS.md)
— esse arquivo **é** versionado, e é dele que o extrator e os testes se guiam.
Os fixtures de teste usam um histórico **anonimizado**, nunca o original.
