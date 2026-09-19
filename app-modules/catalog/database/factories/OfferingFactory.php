<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Models\Offering;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Offering> */
final class OfferingFactory extends Factory
{
    protected $model = Offering::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ofr_class_code' => fake()->numberBetween(1, 8).'SI',
            'ofr_professor_name' => fake()->name(),
            'ofr_schedule' => [
                ['dia' => 'segunda', 'inicio' => '19:30', 'fim' => '21:10', 'sala' => 'Q103'],
            ],
        ];
    }
}
