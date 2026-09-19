<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Database\Factories;

use CampusOs\Tenancy\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Entity> */
final class EntityFactory extends Factory
{
    protected $model = Entity::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ent_name' => fake()->company(),
            'ent_email' => fake()->unique()->safeEmail(),
        ];
    }
}
