<?php

declare(strict_types=1);

namespace CampusOs\Journey\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Journey\Database\Factories\EnrollmentRequestFactory;
use CampusOs\Journey\Enums\DocumentRequestStatus;
use CampusOs\Journey\Observers\EnrollmentRequestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $erq_id
 * @property DocumentRequestStatus $erq_status
 * @property ?array $erq_extraction
 */
#[ObservedBy([EnrollmentRequestObserver::class])]
final class EnrollmentRequest extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'enrollment_requests';

    protected $primaryKey = 'erq_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'erq_created_at';

    public const UPDATED_AT = 'erq_updated_at';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('models.user'), 'user_usr_id', 'usr_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_reg_id', 'reg_id');
    }

    protected static function newFactory(): EnrollmentRequestFactory
    {
        return EnrollmentRequestFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'erq_status' => DocumentRequestStatus::class,
            'erq_extraction' => 'array',
            'erq_parsed_at' => 'datetime',
            'erq_confirmed_at' => 'datetime',
        ];
    }
}
