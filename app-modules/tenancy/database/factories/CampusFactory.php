<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Database\Factories;

use CampusOs\Tenancy\Models\Campus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Campus> */
final class CampusFactory extends Factory
{
    protected $model = Campus::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cps_name' => fake()->city(),
            'cps_code' => strtoupper(fake()->unique()->lexify('??')),
            'cps_city' => fake()->city(),
        ];
    }
}
