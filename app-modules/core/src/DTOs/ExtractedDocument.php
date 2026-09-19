<?php

declare(strict_types=1);

namespace CampusOs\Core\DTOs;

/**
 * O que o extrator leu de um documento acadêmico. Estrutura PLANA e sem regra:
 * nomes e códigos como estão impressos, nada resolvido contra o catálogo.
 *
 * Quem resolve código → disciplina, texto de situação → enum e período → termo
 * é o `journey`, depois da conferência do aluno. Fazer isso aqui acoplaria o
 * provedor de IA ao domínio — exatamente o que o contrato evita.
 */
final readonly class ExtractedDocument
{
    /**
     * @param  'transcript'|'enrollment_request'|'curriculum'|'unknown'  $kind
     * @param  list<ExtractedLine>  $lines
     * @param  array<string, mixed>  $meta  curso, RA, matriz, período… como impressos
     * @param  ?string  $failureReason  recusa de NEGÓCIO (ilegível, não é documento
     *                                  acadêmico). Erro de transporte não vem aqui:
     *                                  lança ExtractorUnavailableException.
     */
    public function __construct(
        public string $kind,
        public array $lines = [],
        public array $meta = [],
        public ?float $confidence = null,
        public ?string $failureReason = null,
        public ?string $provider = null,
    ) {}

    public static function failed(string $reason, ?string $provider = null): self
    {
        return new self('unknown', failureReason: $reason, provider: $provider);
    }

    public function succeeded(): bool
    {
        return $this->failureReason === null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'meta' => $this->meta,
            'lines' => array_map(fn (ExtractedLine $l): array => $l->toArray(), $this->lines),
            'confidence' => $this->confidence,
            'failure_reason' => $this->failureReason,
            'provider' => $this->provider,
        ];
    }
}
