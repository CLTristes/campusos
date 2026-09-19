<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Models\ElectiveGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ElectiveGroup> */
final class ElectiveGroupFactory extends Factory
{
    protected $model = ElectiveGroup::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'elg_code' => (string) fake()->unique()->numberBetween(900, 999),
            'elg_name' => 'Optativas',
            'elg_required_hours' => 210,
            'elg_weekly_hours' => 14,
            'elg_first_term' => 5,
            'elg_last_term' => 8,
        ];
    }
}
