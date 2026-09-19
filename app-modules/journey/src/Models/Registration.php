<?php

declare(strict_types=1);

namespace CampusOs\Journey\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Journey\Database\Factories\RegistrationFactory;
use CampusOs\Journey\Enums\RegistrationStatus;
use CampusOs\Journey\Observers\RegistrationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * O vínculo do aluno com um curso, sob uma matriz congelada no ingresso.
 *
 * @property string $reg_id
 * @property string $reg_number o RA
 * @property RegistrationStatus $reg_status
 */
#[ObservedBy([RegistrationObserver::class])]
final class Registration extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'registrations';

    protected $primaryKey = 'reg_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'reg_created_at';

    public const UPDATED_AT = 'reg_updated_at';

    public const DELETED_AT = 'reg_deleted_at';

    protected $guarded = [];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_std_id', 'std_id');
    }

    /** Curso, matriz e termo vivem no catalog — resolvidos por config. */
    public function course(): BelongsTo
    {
        return $this->belongsTo(config('models.course'), 'course_crs_id', 'crs_id');
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(config('models.curriculum'), 'curriculum_cur_id', 'cur_id');
    }

    public function entryTerm(): BelongsTo
    {
        return $this->belongsTo(config('models.term'), 'entry_term_trm_id', 'trm_id');
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(SubjectEnrollment::class, 'registration_reg_id', 'reg_id');
    }

    /** B7 — puramente informativo, nunca soma na progressão (ver ComplementaryHoursReadModel). */
    public function complementaryActivities(): HasMany
    {
        return $this->hasMany(ComplementaryActivity::class, 'registration_reg_id', 'reg_id');
    }

    protected static function newFactory(): RegistrationFactory
    {
        return RegistrationFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reg_status' => RegistrationStatus::class,
            'reg_graduated_at' => 'date',
        ];
    }
}
