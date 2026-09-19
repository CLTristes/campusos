<?php

declare(strict_types=1);

namespace CampusOs\Journey\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Journey\Database\Factories\StudentFactory;
use CampusOs\Journey\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $std_id
 * @property string $std_name
 */
#[ObservedBy([StudentObserver::class])]
final class Student extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'students';

    protected $primaryKey = 'std_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'std_created_at';

    public const UPDATED_AT = 'std_updated_at';

    public const DELETED_AT = 'std_deleted_at';

    protected $guarded = [];

    /** User vive no tenancy — resolvido por config, sem furar a fronteira. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('models.user'), 'user_usr_id', 'usr_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'student_std_id', 'std_id');
    }

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['std_born_at' => 'date'];
    }
}
