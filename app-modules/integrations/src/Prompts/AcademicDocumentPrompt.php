<?php

declare(strict_types=1);

namespace CampusOs\Integrations\Prompts;

/**
 * O prompt e o schema da extração.
 *
 * Separado do extrator de propósito: o prompt é o artefato que mais muda, e
 * misturá-lo com a mecânica de HTTP faz revisar um custar reler o outro. Se um
 * segundo provedor entrar, este arquivo é reaproveitado.
 *
 * O vocabulário abaixo NÃO é invenção: são os textos que o portal da UTFPR
 * imprime, levantados em docs/dominio/DOCUMENTOS_ACADEMICOS.md.
 */
final class AcademicDocumentPrompt
{
    public const SYSTEM = <<<'TXT'
    Você transcreve documentos acadêmicos brasileiros para dado estruturado.

    Sua única tarefa é LER o que está impresso. Você não interpreta regra
    acadêmica, não calcula nada, não corrige o que parece errado e não
    completa o que está faltando.

    Regras invioláveis:

    1. Copie os valores EXATAMENTE como aparecem. O texto da situação é o do
       documento ("Aprovado Por Nota/Frequência", "Crédito Consignado"), nunca
       uma versão resumida ou traduzida.
    2. Campo ausente ou ilegível é null. NUNCA invente, deduza ou preencha com
       zero. Um "*" na coluna de frequência significa "não se aplica": devolva
       null, não 0.
    3. Nota e frequência usam vírgula decimal no documento ("8,5", "94,4").
       Devolva como número ("8.5", "94.4").
    4. Não invente disciplina que não está no documento, nem repita linha.
    5. Se o arquivo não for um documento acadêmico, devolva
       is_academic_document = false com o motivo — não tente adivinhar.
    TXT;

    public const INSTRUCTION = <<<'TXT'
    Transcreva este documento acadêmico.

    Identifique primeiro o tipo:

    - "transcript" — HISTÓRICO ESCOLAR: lista tudo que o aluno já cursou, com
      situação, nota e frequência por disciplina. Costuma ter quadros de resumo
      de carga horária no fim.
    - "enrollment_request" — REQUERIMENTO / CONFIRMAÇÃO DE MATRÍCULA: só as
      disciplinas do semestre atual, com turma e grade de horários, sem notas.
    - "curriculum" — MATRIZ CURRICULAR: a grade do CURSO (não de um aluno),
      com período, carga horária e pré-requisitos.

    Em "meta", copie o que o cabeçalho trouxer: student_name,
    registration_number (o RA), course, course_code, curriculum_code, campus,
    entry_term, current_period, status.

    Em "lines", uma entrada por disciplina listada. Para histórico, inclua TODAS
    as tabelas de disciplina (obrigatórias, optativas, enriquecimento
    curricular) — não pare na primeira.
    TXT;

    /**
     * O schema que a resposta deve casar. Com `responseSchema` o modelo devolve
     * JSON válido nesta forma — sem pedir educadamente no prompt e sem parser
     * tolerante do nosso lado.
     *
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        return [
            'type' => 'OBJECT',
            'required' => ['is_academic_document', 'kind', 'lines'],
            'properties' => [
                'is_academic_document' => ['type' => 'BOOLEAN'],
                'failure_reason' => [
                    'type' => 'STRING',
                    'nullable' => true,
                    'description' => 'Preenchido só quando is_academic_document é false.',
                ],
                'kind' => [
                    'type' => 'STRING',
                    'enum' => ['transcript', 'enrollment_request', 'curriculum', 'unknown'],
                ],
                'confidence' => [
                    'type' => 'NUMBER',
                    'nullable' => true,
                    'description' => 'De 0 a 1: o quanto a leitura foi legível.',
                ],
                'meta' => [
                    'type' => 'OBJECT',
                    'nullable' => true,
                    'properties' => [
                        'student_name' => ['type' => 'STRING', 'nullable' => true],
                        'registration_number' => ['type' => 'STRING', 'nullable' => true],
                        'course' => ['type' => 'STRING', 'nullable' => true],
                        'course_code' => ['type' => 'STRING', 'nullable' => true],
                        'curriculum_code' => ['type' => 'STRING', 'nullable' => true],
                        'campus' => ['type' => 'STRING', 'nullable' => true],
                        'entry_term' => ['type' => 'STRING', 'nullable' => true],
                        'current_period' => ['type' => 'INTEGER', 'nullable' => true],
                        'status' => ['type' => 'STRING', 'nullable' => true],
                    ],
                ],
                'lines' => [
                    'type' => 'ARRAY',
                    'items' => [
                        'type' => 'OBJECT',
                        'required' => ['code'],
                        'properties' => [
                            'code' => [
                                'type' => 'STRING',
                                'description' => 'Código da disciplina como impresso: ARC102, MAT032.',
                            ],
                            'name' => ['type' => 'STRING', 'nullable' => true],
                            'year' => ['type' => 'INTEGER', 'nullable' => true],
                            'period' => [
                                'type' => 'INTEGER',
                                'nullable' => true,
                                'description' => 'Semestre do ano: 1 ou 2.',
                            ],
                            'class_code' => [
                                'type' => 'STRING',
                                'nullable' => true,
                                'description' => 'Turma: 5SI, ESTAGIO, OPTATIVA.',
                            ],
                            'status' => [
                                'type' => 'STRING',
                                'nullable' => true,
                                'description' => 'O texto EXATO do documento.',
                            ],
                            'grade' => ['type' => 'NUMBER', 'nullable' => true],
                            'attendance' => [
                                'type' => 'NUMBER',
                                'nullable' => true,
                                'description' => 'Frequência em %. "*" no documento vira null, nunca 0.',
                            ],
                            'hours' => [
                                'type' => 'INTEGER',
                                'nullable' => true,
                                'description' => 'Carga horária total (CHT).',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
