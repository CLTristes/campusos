<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Enums\SubjectNature;
use CampusOs\Catalog\Models\CurriculumSubject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CurriculumSubject> */
final class CurriculumSubjectFactory extends Factory
{
    protected $model = CurriculumSubject::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'cbs_term' => fake()->numberBetween(1, 8),
            'cbs_nature' => SubjectNature::Mandatory,
        ];
    }

    public function elective(): static
    {
        return $this->state(fn (): array => ['cbs_nature' => SubjectNature::Elective]);
    }
}
