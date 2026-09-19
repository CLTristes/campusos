<?php

declare(strict_types=1);

namespace CampusOs\Integrations\Extractors;

use CampusOs\Core\Contracts\AcademicDocumentExtractor;
use CampusOs\Core\DTOs\ExtractedDocument;
use CampusOs\Core\DTOs\ExtractedLine;
use CampusOs\Core\Exceptions\ExtractorUnavailableException;
use CampusOs\Integrations\Prompts\AcademicDocumentPrompt;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;
use Throwable;

/**
 * Leitura de documento acadêmico pelo Gemini (Google AI Studio).
 *
 * **Por que Gemini e não outro:** a API tem camada gratuita que cobre um MVP
 * inteiro, e é a única decisão de provedor do sistema. Ela mora AQUI e em mais
 * nenhum lugar — `journey` fala com `AcademicDocumentExtractor`, não com o
 * Google. Trocar de provedor é escrever outra implementação e mudar o bind em
 * `IntegrationsServiceProvider`.
 *
 * **Sem SDK, HTTP direto:** a API é um POST com JSON, e o client do Laravel já
 * dá timeout, retry e teste (`Http::fake`). Um SDK aqui seria dependência a
 * mais para um único endpoint.
 *
 * **Saída estruturada por schema, não por pedido educado.** `responseMimeType:
 * application/json` + `responseSchema` fazem o modelo devolver JSON que casa com
 * a estrutura declarada — sem "responda só JSON, por favor" no prompt e sem
 * parser tolerante do nosso lado.
 */
final class GeminiDocumentExtractor implements AcademicDocumentExtractor
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    public function name(): string
    {
        return "gemini:{$this->model}";
    }

    public function extract(string $absolutePath, string $mimeType): ExtractedDocument
    {
        if ($this->apiKey === '') {
            // Credencial ausente é indisponibilidade, não documento ilegível:
            // marcar o documento como falho apagaria o upload do aluno por um
            // erro de configuração nosso.
            throw new ExtractorUnavailableException('GEMINI_API_KEY não configurada.');
        }

        $bytes = @file_get_contents($absolutePath);

        if ($bytes === false) {
            throw new ExtractorUnavailableException("Arquivo ilegível em disco: {$absolutePath}");
        }

        $response = $this->call([
            'contents' => [[
                'parts' => [
                    ['inline_data' => ['mime_type' => $mimeType, 'data' => base64_encode($bytes)]],
                    ['text' => AcademicDocumentPrompt::INSTRUCTION],
                ],
            ]],
            'systemInstruction' => ['parts' => [['text' => AcademicDocumentPrompt::SYSTEM]]],
            'generationConfig' => [
                // Extração é transcrição: queremos a leitura mais provável,
                // sempre a mesma para o mesmo documento.
                'temperature' => 0.0,
                'responseMimeType' => 'application/json',
                'responseSchema' => AcademicDocumentPrompt::schema(),
            ],
        ]);

        return $this->toDocument($response);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function call(array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                // Só o que é transporte entra no retry. Recusa do modelo não é
                // erro de rede e não se resolve tentando de novo.
                ->retry(2, 1500, fn (Throwable $e): bool => $e instanceof ConnectionException, throw: false)
                ->withHeaders(['x-goog-api-key' => $this->apiKey])
                ->post("{$this->baseUrl}/models/{$this->model}:generateContent", $payload);
        } catch (ConnectionException $e) {
            throw new ExtractorUnavailableException('Falha de conexão com o Gemini.', previous: $e);
        }

        if ($response->failed()) {
            // 4xx de credencial/cota e 5xx são todos indisponibilidade do nosso
            // lado do contrato — o Job reprocessa.
            throw new ExtractorUnavailableException(
                "Gemini respondeu {$response->status()}: ".mb_substr($response->body(), 0, 300)
            );
        }

        return $response->json() ?? [];
    }

    /** @param array<string, mixed> $response */
    private function toDocument(array $response): ExtractedDocument
    {
        $text = data_get($response, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            // Resposta vazia costuma ser bloqueio de segurança ou corte por
            // token — indisponibilidade, não documento ruim.
            $reason = data_get($response, 'candidates.0.finishReason', 'sem conteúdo');

            throw new ExtractorUnavailableException("Gemini devolveu resposta vazia ({$reason}).");
        }

        try {
            /** @var array<string, mixed> $parsed */
            $parsed = json_decode($text, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ExtractorUnavailableException('Gemini devolveu JSON inválido.', previous: $e);
        }

        // Recusa de NEGÓCIO: o modelo leu e concluiu que não é documento
        // acadêmico. Estado final, sem retry — volta no DTO, não como exceção.
        if (($parsed['is_academic_document'] ?? true) === false) {
            return ExtractedDocument::failed(
                (string) ($parsed['failure_reason'] ?? 'O arquivo não parece um documento acadêmico.'),
                $this->name(),
            );
        }

        $lines = array_map(
            static fn (array $row): ExtractedLine => ExtractedLine::fromArray($row),
            array_values(array_filter(
                $parsed['lines'] ?? [],
                static fn (mixed $row): bool => is_array($row) && ! empty($row['code']),
            )),
        );

        if ($lines === []) {
            return ExtractedDocument::failed(
                'Nenhuma disciplina foi encontrada no documento.',
                $this->name(),
            );
        }

        return new ExtractedDocument(
            kind: (string) ($parsed['kind'] ?? 'unknown'),
            lines: $lines,
            meta: (array) ($parsed['meta'] ?? []),
            confidence: isset($parsed['confidence']) ? (float) $parsed['confidence'] : null,
            provider: $this->name(),
        );
    }
}
