<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Database\Factories;

use CampusOs\Lifeos\Enums\TaskKind;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Task> */
final class TaskFactory extends Factory
{
    protected $model = Task::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tsk_title' => fake()->sentence(4),
            'tsk_description_md' => fake()->boolean(70) ? fake()->paragraph() : null,
            'tsk_status' => TaskStatus::Todo->value,
            'tsk_kind' => fake()->randomElement(TaskKind::cases())->value,
            'tsk_due_at' => fake()->dateTimeBetween('now', '+30 days'),
            'tsk_visibility' => Visibility::Private->value,
        ];
    }
}
