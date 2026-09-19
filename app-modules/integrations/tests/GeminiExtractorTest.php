<?php

declare(strict_types=1);

use CampusOs\Core\Contracts\AcademicDocumentExtractor;
use CampusOs\Core\Exceptions\ExtractorUnavailableException;
use CampusOs\Integrations\Extractors\GeminiDocumentExtractor;
use CampusOs\Integrations\Extractors\NullDocumentExtractor;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * O extrator do Gemini — testado com Http::fake, sem tocar a rede.
 *
 * O que estes testes protegem, acima de tudo, é a distinção entre erro de
 * TRANSPORTE (lança, a fila reprocessa) e recusa de NEGÓCIO (estado final).
 * Errar essa fronteira transforma uma instabilidade de 30 segundos num
 * documento marcado como ilegível para sempre.
 */
function gemini(): GeminiDocumentExtractor
{
    return new GeminiDocumentExtractor(
        apiKey: 'chave-de-teste',
        model: 'gemini-2.0-flash',
        baseUrl: 'https://generativelanguage.googleapis.com/v1beta',
        timeout: 5,
    );
}

function geminiResponde(array $payload): void
{
    Http::fake([
        '*generativelanguage*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => json_encode($payload)]]],
                'finishReason' => 'STOP',
            ]],
        ]),
    ]);
}

function pdfFalso(): string
{
    $path = sys_get_temp_dir().'/campusos-teste-'.uniqid().'.pdf';
    file_put_contents($path, '%PDF-1.4 fake');

    return $path;
}

it('transcreve um histórico em linhas estruturadas', function () {
    geminiResponde([
        'is_academic_document' => true,
        'kind' => 'transcript',
        'confidence' => 0.96,
        'meta' => ['registration_number' => '2567857', 'course' => 'Sist. Informação'],
        'lines' => [
            ['code' => 'ARC102', 'name' => 'Arquitetura de Computadores', 'year' => 2023,
                'period' => 1, 'status' => 'Aprovado Por Nota/Frequência', 'grade' => 8.5, 'attendance' => 91.2],
        ],
    ]);

    $doc = gemini()->extract(pdfFalso(), 'application/pdf');

    expect($doc->succeeded())->toBeTrue()
        ->and($doc->kind)->toBe('transcript')
        ->and($doc->provider)->toBe('gemini:gemini-2.0-flash')
        ->and($doc->meta['registration_number'])->toBe('2567857')
        ->and($doc->lines)->toHaveCount(1)
        ->and($doc->lines[0]->code)->toBe('ARC102')
        ->and($doc->lines[0]->grade)->toBe(8.5)
        ->and($doc->lines[0]->attendance)->toBe(91.2);
});

it('frequência "*" vira null, nunca zero', function () {
    // No documento, "*" significa NÃO SE APLICA (exame de suficiência,
    // estágio). Virar zero reprovaria por falta quem foi aprovado.
    geminiResponde([
        'is_academic_document' => true,
        'kind' => 'transcript',
        'lines' => [['code' => 'RED202', 'status' => 'Aprovado Em Exame De Suficiência',
            'grade' => 6.0, 'attendance' => '*']],
    ]);

    expect(gemini()->extract(pdfFalso(), 'application/pdf')->lines[0]->attendance)->toBeNull();
});

it('aceita vírgula decimal, que é como o documento imprime', function () {
    geminiResponde([
        'is_academic_document' => true,
        'kind' => 'transcript',
        'lines' => [['code' => 'EST003', 'status' => 'Aprovado', 'grade' => '7,5', 'attendance' => '82,8']],
    ]);

    $linha = gemini()->extract(pdfFalso(), 'application/pdf')->lines[0];

    expect($linha->grade)->toBe(7.5)->and($linha->attendance)->toBe(82.8);
});

it('arquivo que não é documento acadêmico é recusa de NEGÓCIO, não exceção', function () {
    geminiResponde([
        'is_academic_document' => false,
        'kind' => 'unknown',
        'failure_reason' => 'A imagem é a foto de um gato.',
        'lines' => [],
    ]);

    $doc = gemini()->extract(pdfFalso(), 'application/pdf');

    // Estado final: tentar de novo daria o mesmo resultado.
    expect($doc->succeeded())->toBeFalse()
        ->and($doc->failureReason)->toBe('A imagem é a foto de um gato.');
});

it('documento sem nenhuma disciplina também é recusa de negócio', function () {
    geminiResponde(['is_academic_document' => true, 'kind' => 'transcript', 'lines' => []]);

    expect(gemini()->extract(pdfFalso(), 'application/pdf')->failureReason)
        ->toContain('Nenhuma disciplina');
});

it('5xx é TRANSPORTE — lança para a fila reprocessar', function () {
    Http::fake(['*generativelanguage*' => Http::response('upstream error', 503)]);

    expect(fn () => gemini()->extract(pdfFalso(), 'application/pdf'))
        ->toThrow(ExtractorUnavailableException::class);
});

it('cota estourada é TRANSPORTE, não documento ruim', function () {
    // 429 num provedor gratuito é o caso mais provável de todos. Marcar o
    // documento como ilegível aqui apagaria o upload do aluno por um limite
    // nosso — o certo é a fila tentar de novo.
    Http::fake(['*generativelanguage*' => Http::response(['error' => 'quota'], 429)]);

    expect(fn () => gemini()->extract(pdfFalso(), 'application/pdf'))
        ->toThrow(ExtractorUnavailableException::class);
});

it('resposta vazia (bloqueio ou corte) é transporte', function () {
    Http::fake(['*generativelanguage*' => Http::response([
        'candidates' => [['content' => ['parts' => []], 'finishReason' => 'SAFETY']],
    ])]);

    expect(fn () => gemini()->extract(pdfFalso(), 'application/pdf'))
        ->toThrow(ExtractorUnavailableException::class);
});

it('credencial ausente é transporte, não documento ilegível', function () {
    $semChave = new GeminiDocumentExtractor('', 'gemini-2.0-flash', 'https://x', 5);

    expect(fn () => $semChave->extract(pdfFalso(), 'application/pdf'))
        ->toThrow(ExtractorUnavailableException::class, 'GEMINI_API_KEY');
});

it('manda o documento e o schema na requisição', function () {
    geminiResponde(['is_academic_document' => true, 'kind' => 'transcript',
        'lines' => [['code' => 'X']]]);

    gemini()->extract(pdfFalso(), 'application/pdf');

    Http::assertSent(function (Request $r): bool {
        $body = $r->data();

        return $r->hasHeader('x-goog-api-key')
            && str_contains($r->url(), 'gemini-2.0-flash:generateContent')
            && $body['contents'][0]['parts'][0]['inline_data']['mime_type'] === 'application/pdf'
            // Saída estruturada por schema, não por pedido educado no prompt.
            && $body['generationConfig']['responseMimeType'] === 'application/json'
            && isset($body['generationConfig']['responseSchema'])
            // Transcrição quer a leitura mais provável, sempre igual.
            && $body['generationConfig']['temperature'] === 0.0;
    });
});

it('o bind resolve o provedor da config — trocar não toca o domínio', function () {
    config(['extractors.default' => 'null']);
    expect(app(AcademicDocumentExtractor::class))->toBeInstanceOf(NullDocumentExtractor::class);

    config(['extractors.default' => 'gemini']);
    app()->forgetInstance(AcademicDocumentExtractor::class);
    expect(app(AcademicDocumentExtractor::class))->toBeInstanceOf(GeminiDocumentExtractor::class);
});

it('provedor desconhecido falha alto, na hora', function () {
    config(['extractors.default' => 'inventado']);
    app()->forgetInstance(AcademicDocumentExtractor::class);

    expect(fn () => app(AcademicDocumentExtractor::class))
        ->toThrow(InvalidArgumentException::class, 'inventado');
});
