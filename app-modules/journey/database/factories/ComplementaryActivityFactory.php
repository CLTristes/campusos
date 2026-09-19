<?php

declare(strict_types=1);

namespace CampusOs\Journey\Database\Factories;

use CampusOs\Journey\Enums\ActivityStatus;
use CampusOs\Journey\Models\ComplementaryActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ComplementaryActivity> */
final class ComplementaryActivityFactory extends Factory
{
    protected $model = ComplementaryActivity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cac_title' => fake()->sentence(3),
            'cac_hours_claimed' => fake()->numberBetween(4, 40),
            'cac_status' => ActivityStatus::Submitted,
            'cac_issued_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
