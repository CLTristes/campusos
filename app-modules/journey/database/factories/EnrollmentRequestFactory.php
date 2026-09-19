<?php

declare(strict_types=1);

namespace CampusOs\Journey\Database\Factories;

use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Models\EnrollmentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EnrollmentRequest> */
final class EnrollmentRequestFactory extends Factory
{
    protected $model = EnrollmentRequest::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'erq_kind' => 'transcript',
            'erq_file_path' => 'academic-documents/fake.pdf',
            'erq_mime' => 'application/pdf',
            'erq_status' => DocumentRequestStatus::Uploaded,
        ];
    }
}
