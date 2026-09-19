<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Enums\CourseDegree;
use CampusOs\Catalog\Enums\CourseShift;
use CampusOs\Catalog\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Course> */
final class CourseFactory extends Factory
{
    protected $model = Course::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'crs_code' => (string) fake()->unique()->numberBetween(10, 99),
            'crs_name' => 'Bacharelado em '.fake()->words(2, true),
            'crs_degree' => CourseDegree::Bachelor,
            'crs_shift' => CourseShift::Evening,
        ];
    }
}
