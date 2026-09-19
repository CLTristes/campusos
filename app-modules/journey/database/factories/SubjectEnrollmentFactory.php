<?php

declare(strict_types=1);

namespace CampusOs\Journey\Database\Factories;

use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Models\SubjectEnrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubjectEnrollment> */
final class SubjectEnrollmentFactory extends Factory
{
    protected $model = SubjectEnrollment::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'sen_status' => EnrollmentStatus::Approved,
            'sen_grade' => 8.0,
            'sen_attendance' => 90.0,
            'sen_source' => EnrollmentSource::Transcript,
        ];
    }

    public function approved(int $hours = 60): static
    {
        return $this->state(fn (): array => [
            'sen_status' => EnrollmentStatus::Approved,
            'sen_hours_earned' => $hours,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'sen_status' => EnrollmentStatus::FailedGrade,
            'sen_grade' => 3.0,
            'sen_hours_earned' => 0,
        ]);
    }

    public function enrolled(): static
    {
        return $this->state(fn (): array => [
            'sen_status' => EnrollmentStatus::Enrolled,
            'sen_grade' => null,
            'sen_attendance' => null,
            'sen_hours_earned' => 0,
        ]);
    }
}
