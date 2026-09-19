<?php

declare(strict_types=1);

namespace CampusOs\Journey\Database\Factories;

use CampusOs\Journey\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Student> */
final class StudentFactory extends Factory
{
    protected $model = Student::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'std_name' => fake()->name(),
            'std_document' => (string) fake()->unique()->numberBetween(10000000000, 99999999999),
        ];
    }
}
