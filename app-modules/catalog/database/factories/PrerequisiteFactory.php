<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Database\Factories;

use CampusOs\Catalog\Enums\PrerequisiteType;
use CampusOs\Catalog\Models\Prerequisite;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Prerequisite> */
final class PrerequisiteFactory extends Factory
{
    protected $model = Prerequisite::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['prq_type' => PrerequisiteType::Subject];
    }

    /** O caso do EST501: exige estar no Nº período, não uma disciplina. */
    public function minimumTerm(int $term): static
    {
        return $this->state(fn (): array => [
            'prq_type' => PrerequisiteType::MinimumTerm,
            'required_cbs_id' => null,
            'prq_min_term' => $term,
        ]);
    }
}
