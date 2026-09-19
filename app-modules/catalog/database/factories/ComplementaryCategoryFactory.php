<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Models\ComplementaryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ComplementaryCategory> */
final class ComplementaryCategoryFactory extends Factory
{
    protected $model = ComplementaryCategory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ccg_name' => fake()->unique()->words(2, true),
            'ccg_max_hours' => fake()->randomElement([40, 60, 80, 100]),
        ];
    }
}
