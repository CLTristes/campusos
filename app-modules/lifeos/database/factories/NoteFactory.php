<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Database\Factories;

use CampusOs\Lifeos\Enums\NoteKind;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Note> */
final class NoteFactory extends Factory
{
    protected $model = Note::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'nte_title' => fake()->sentence(4),
            'nte_body_md' => fake()->paragraphs(3, true),
            'nte_kind' => fake()->randomElement(NoteKind::cases())->value,
            'nte_visibility' => Visibility::Private->value,
        ];
    }
}
