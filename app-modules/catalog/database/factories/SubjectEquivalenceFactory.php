<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Models\SubjectEquivalence;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubjectEquivalence> */
final class SubjectEquivalenceFactory extends Factory
{
    protected $model = SubjectEquivalence::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'seq_code' => strtoupper(fake()->unique()->bothify('??##?')),
            'seq_hours' => 60,
            'seq_group' => null,
        ];
    }
}
