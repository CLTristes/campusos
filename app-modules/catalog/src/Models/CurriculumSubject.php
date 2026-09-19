<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\CurriculumSubjectFactory;
use CampusOs\Catalog\Enums\SubjectNature;
use CampusOs\Catalog\Observers\CurriculumSubjectObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A disciplina DENTRO de uma matriz.
 *
 * @property string $cbs_id
 * @property int $cbs_term período SUGERIDO — não onde o aluno cursou
 */
#[ObservedBy([CurriculumSubjectObserver::class])]
final class CurriculumSubject extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'curriculum_subjects';

    protected $primaryKey = 'cbs_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'cbs_created_at';

    public const UPDATED_AT = 'cbs_updated_at';

    protected $guarded = [];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_cur_id', 'cur_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_sbj_id', 'sbj_id');
    }

    public function electiveGroup(): BelongsTo
    {
        return $this->belongsTo(ElectiveGroup::class, 'elective_group_elg_id', 'elg_id');
    }

    /** As arestas que ESTA disciplina exige para ser cursada. */
    public function prerequisites(): HasMany
    {
        return $this->hasMany(Prerequisite::class, 'curriculum_subject_cbs_id', 'cbs_id');
    }

    /** As disciplinas que ESTA destrava — o que uma reprovação aqui trava. */
    public function unlocks(): HasMany
    {
        return $this->hasMany(Prerequisite::class, 'required_cbs_id', 'cbs_id');
    }

    /** Disciplinas de outra matriz que valem por esta (crédito consignado). */
    public function equivalences(): HasMany
    {
        return $this->hasMany(SubjectEquivalence::class, 'curriculum_subject_cbs_id', 'cbs_id');
    }

    protected static function newFactory(): CurriculumSubjectFactory
    {
        return CurriculumSubjectFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['cbs_nature' => SubjectNature::class];
    }
}
