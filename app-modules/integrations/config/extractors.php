<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Extrator de documentos acadêmicos
    |--------------------------------------------------------------------------
    |
    | Qual implementação do contrato AcademicDocumentExtractor o sistema usa.
    | Trocar de provedor é trocar ESTA chave — nenhuma linha de domínio muda,
    | porque o journey fala com o contrato, não com o Google.
    |
    | 'gemini' — Google AI Studio. Escolhido para o MVP pela camada gratuita.
    | 'null'   — não chama ninguém e marca o documento como não processado.
    |            É o que roda nos testes e o que permite demonstrar o fluxo
    |            inteiro com a rede caída.
    |
    */
    'default' => env('DOCUMENT_EXTRACTOR', 'gemini'),

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        // Histórico de 6 páginas leva alguns segundos; o Job tem tempo, o
        // request HTTP do aluno não espera por isso (202 + fila).
        'timeout' => (int) env('GEMINI_TIMEOUT', 120),
    ],
];
