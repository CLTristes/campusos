<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Enums\SubjectModel;
use CampusOs\Catalog\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subject> */
final class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'sbj_code' => strtoupper(fake()->unique()->lexify('???')).fake()->numberBetween(100, 899),
            'sbj_name' => ucfirst(fake()->words(3, true)),
            'sbj_model' => SubjectModel::BasicScientific,
            'sbj_weekly_hours' => 4,
            'sbj_hours' => 60,
            'sbj_extension_hours' => 0,
        ];
    }

    /** Disciplina que carrega carga extensionista embutida (PEX304, HSA003…). */
    public function withExtension(int $hours = 30): static
    {
        return $this->state(fn (): array => [
            'sbj_hours' => $hours,
            'sbj_extension_hours' => $hours,
        ]);
    }
}
