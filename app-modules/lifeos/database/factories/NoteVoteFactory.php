<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Database\Factories;

use CampusOs\Lifeos\Models\NoteVote;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NoteVote> */
final class NoteVoteFactory extends Factory
{
    protected $model = NoteVote::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [];
    }
}
