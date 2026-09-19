<?php

declare(strict_types=1);

namespace CampusOs\Core\DTOs;

/**
 * Uma linha de disciplina lida do documento — os campos EXATAMENTE como
 * impressos. `status` é o texto do documento ("Aprovado Por Nota/Frequência"),
 * não um enum: traduzir é trabalho do `journey`.
 */
final readonly class ExtractedLine
{
    public function __construct(
        public string $code,
        public ?string $name = null,
        public ?int $year = null,
        public ?int $period = null,
        public ?string $classCode = null,
        public ?string $status = null,
        public ?float $grade = null,
        public ?float $attendance = null,
        public ?int $hours = null,
    ) {}

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        $num = static fn (mixed $v): ?float => $v === null || $v === '' || $v === '*'
            ? null
            : (float) str_replace(',', '.', (string) $v);

        return new self(
            code: trim((string) ($row['code'] ?? '')),
            name: isset($row['name']) ? trim((string) $row['name']) : null,
            year: isset($row['year']) ? (int) $row['year'] : null,
            period: isset($row['period']) ? (int) $row['period'] : null,
            classCode: isset($row['class_code']) ? trim((string) $row['class_code']) : null,
            status: isset($row['status']) ? trim((string) $row['status']) : null,
            grade: $num($row['grade'] ?? null),
            // "*" no documento significa NÃO SE APLICA, nunca zero — ver
            // docs/dominio/PROGRESSAO.md regra 6.
            attendance: $num($row['attendance'] ?? null),
            hours: isset($row['hours']) ? (int) $row['hours'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'year' => $this->year,
            'period' => $this->period,
            'class_code' => $this->classCode,
            'status' => $this->status,
            'grade' => $this->grade,
            'attendance' => $this->attendance,
            'hours' => $this->hours,
        ];
    }
}
