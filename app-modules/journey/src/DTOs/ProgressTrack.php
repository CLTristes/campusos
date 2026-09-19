<?php

declare(strict_types=1);

namespace CampusOs\Journey\DTOs;

/**
 * Uma faixa da barra de progresso. Satura no próprio teto de propósito
 * (docs/dominio/PROGRESSAO.md regra 8): horas de uma natureza não escorrem
 * para outra, e o excedente não pode inflar o total.
 */
final readonly class ProgressTrack
{
    public function __construct(
        public string $kind,
        public string $label,
        public int $completed,
        public int $required,
        public int $inProgress = 0,
    ) {}

    /** O que de fato conta — nunca mais que o exigido. */
    public function counted(): int
    {
        return min($this->completed, $this->required);
    }

    /** Horas cumpridas ALÉM do teto. Contam para o aluno saber; não para o total. */
    public function surplus(): int
    {
        return max(0, $this->completed - $this->required);
    }

    public function remaining(): int
    {
        return max(0, $this->required - $this->completed);
    }

    public function percentage(): float
    {
        return $this->required === 0 ? 100.0 : round(($this->counted() / $this->required) * 100, 1);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'label' => $this->label,
            'completed' => $this->completed,
            'counted' => $this->counted(),
            'required' => $this->required,
            'in_progress' => $this->inProgress,
            'remaining' => $this->remaining(),
            'surplus' => $this->surplus(),
            'percentage' => $this->percentage(),
        ];
    }
}
