<?php

declare(strict_types=1);

namespace CampusOs\Journey\Database\Factories;

use CampusOs\Journey\Enums\RegistrationStatus;
use CampusOs\Journey\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Registration> */
final class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'reg_number' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'reg_status' => RegistrationStatus::Active,
        ];
    }
}
