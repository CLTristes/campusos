<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\SubjectEquivalenceFactory;
use CampusOs\Catalog\Observers\SubjectEquivalenceObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $seq_id
 * @property string $seq_code código impresso no documento (matriz antiga)
 * @property ?int $seq_group null = satisfaz sozinha · N = só o grupo inteiro
 */
#[ObservedBy([SubjectEquivalenceObserver::class])]
final class SubjectEquivalence extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'subject_equivalences';

    protected $primaryKey = 'seq_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'seq_created_at';

    public const UPDATED_AT = 'seq_updated_at';

    protected $guarded = [];

    /** Sozinha basta, ou precisa do grupo inteiro? */
    public function satisfiesAlone(): bool
    {
        return $this->seq_group === null;
    }

    public function curriculumSubject(): BelongsTo
    {
        return $this->belongsTo(CurriculumSubject::class, 'curriculum_subject_cbs_id', 'cbs_id');
    }

    protected static function newFactory(): SubjectEquivalenceFactory
    {
        return SubjectEquivalenceFactory::new();
    }
}
