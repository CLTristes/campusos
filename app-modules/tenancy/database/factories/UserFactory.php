<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Database\Factories;

use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'usr_name' => fake()->name(),
            'usr_email' => fake()->unique()->safeEmail(),
            'usr_password' => 'senha-de-teste',
            'usr_role' => UserRole::Student,
            'usr_registration_number' => (string) fake()->unique()->numberBetween(1000000, 9999999),
        ];
    }

    public function coordinator(): static
    {
        return $this->state(fn (): array => [
            'usr_role' => UserRole::Coordinator,
            'usr_registration_number' => null,
        ]);
    }
}
