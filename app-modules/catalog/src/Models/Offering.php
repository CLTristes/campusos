<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\OfferingFactory;
use CampusOs\Catalog\Observers\OfferingObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A turma: disciplina × semestre × código de turma × câmpus.
 *
 * @property string $ofr_class_code "5SI", "ESTAGIO", "OPBSI"
 * @property ?array $ofr_schedule
 */
#[ObservedBy([OfferingObserver::class])]
final class Offering extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'offerings';

    protected $primaryKey = 'ofr_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'ofr_created_at';

    public const UPDATED_AT = 'ofr_updated_at';

    public const DELETED_AT = 'ofr_deleted_at';

    protected $guarded = [];

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_trm_id', 'trm_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_sbj_id', 'sbj_id');
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(config('models.campus'), 'campus_cps_id', 'cps_id');
    }

    protected static function newFactory(): OfferingFactory
    {
        return OfferingFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['ofr_schedule' => 'array'];
    }
}
