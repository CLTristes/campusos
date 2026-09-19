<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Enums\CurriculumStatus;
use CampusOs\Catalog\Models\Curriculum;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Curriculum> */
final class CurriculumFactory extends Factory
{
    protected $model = Curriculum::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cur_code' => (string) fake()->unique()->numberBetween(10, 99),
            'cur_name' => fake()->words(3, true),
            'cur_version' => 1,
            'cur_status' => CurriculumStatus::Active,
            'cur_effective_from' => '2023-01-01',
            'cur_mandatory_hours' => 2730,
            'cur_elective_hours' => 210,
            'cur_standalone_extension_hours' => 60,
            'cur_extension_hours' => 300,
            'cur_expected_terms' => 8,
            'cur_max_term_hours' => 390,
            'cur_max_weekly_deficit' => 16,
        ];
    }
}
