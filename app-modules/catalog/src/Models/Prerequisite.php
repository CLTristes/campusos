<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\PrerequisiteFactory;
use CampusOs\Catalog\Enums\PrerequisiteType;
use CampusOs\Catalog\Observers\PrerequisiteObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Uma aresta do grafo de dependência da matriz.
 *
 * @property string $prq_id
 * @property PrerequisiteType $prq_type
 */
#[ObservedBy([PrerequisiteObserver::class])]
final class Prerequisite extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'prerequisites';

    protected $primaryKey = 'prq_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'prq_created_at';

    public const UPDATED_AT = 'prq_updated_at';

    protected $guarded = [];

    public function curriculumSubject(): BelongsTo
    {
        return $this->belongsTo(CurriculumSubject::class, 'curriculum_subject_cbs_id', 'cbs_id');
    }

    public function required(): BelongsTo
    {
        return $this->belongsTo(CurriculumSubject::class, 'required_cbs_id', 'cbs_id');
    }

    protected static function newFactory(): PrerequisiteFactory
    {
        return PrerequisiteFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['prq_type' => PrerequisiteType::class];
    }
}
