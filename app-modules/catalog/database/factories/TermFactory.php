<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Enums\TermStatus;
use CampusOs\Catalog\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Term> */
final class TermFactory extends Factory
{
    protected $model = Term::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'trm_year' => 2026,
            'trm_period' => 1,
            'trm_status' => TermStatus::Closed,
        ];
    }

    public function of(int $year, int $period): static
    {
        return $this->state(fn (): array => ['trm_year' => $year, 'trm_period' => $period]);
    }
}
