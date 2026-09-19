<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\CourseFactory;
use CampusOs\Catalog\Enums\CourseDegree;
use CampusOs\Catalog\Enums\CourseShift;
use CampusOs\Catalog\Observers\CourseObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $crs_id
 * @property string $crs_code
 * @property string $crs_name
 */
#[ObservedBy([CourseObserver::class])]
final class Course extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'courses';

    protected $primaryKey = 'crs_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'crs_created_at';

    public const UPDATED_AT = 'crs_updated_at';

    public const DELETED_AT = 'crs_deleted_at';

    protected $guarded = [];

    /** Campus vive no tenancy — resolvido por config para não furar a fronteira. */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(config('models.campus'), 'campus_cps_id', 'cps_id');
    }

    public function curricula(): HasMany
    {
        return $this->hasMany(Curriculum::class, 'course_crs_id', 'crs_id');
    }

    protected static function newFactory(): CourseFactory
    {
        return CourseFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'crs_degree' => CourseDegree::class,
            'crs_shift' => CourseShift::class,
        ];
    }
}
