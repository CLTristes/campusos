<?php

declare(strict_types=1);

namespace CampusOs\Journey\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Journey\Database\Factories\SubjectEnrollmentFactory;
use CampusOs\Journey\Enums\EnrollmentSource;
use CampusOs\Journey\Enums\EnrollmentStatus;
use CampusOs\Journey\Observers\SubjectEnrollmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Uma linha do histórico: o aluno cursou TAL disciplina em TAL semestre.
 *
 * @property string $sen_id
 * @property EnrollmentStatus $sen_status
 * @property ?float $sen_grade
 * @property ?float $sen_attendance null = não se aplica (nunca zero)
 * @property int $sen_hours_earned
 */
#[ObservedBy([SubjectEnrollmentObserver::class])]
final class SubjectEnrollment extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'subject_enrollments';

    protected $primaryKey = 'sen_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'sen_created_at';

    public const UPDATED_AT = 'sen_updated_at';

    public const DELETED_AT = 'sen_deleted_at';

    protected $guarded = [];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_reg_id', 'reg_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(config('models.subject'), 'subject_sbj_id', 'sbj_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(config('models.term'), 'term_trm_id', 'trm_id');
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(config('models.offering'), 'offering_ofr_id', 'ofr_id');
    }

    protected static function newFactory(): SubjectEnrollmentFactory
    {
        return SubjectEnrollmentFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sen_status' => EnrollmentStatus::class,
            'sen_source' => EnrollmentSource::class,
            'sen_grade' => 'float',
            'sen_attendance' => 'float',
        ];
    }
}
