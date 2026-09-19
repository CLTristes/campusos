<?php

declare(strict_types=1);

namespace CampusOs\Journey\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Journey\Database\Factories\ComplementaryActivityFactory;
use CampusOs\Journey\Enums\ActivityStatus;
use CampusOs\Journey\Observers\ComplementaryActivityObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * O tracker do aluno para uma atividade complementar (B7) — puramente
 * informativo, nunca soma no `ProgressReadModel` (ver
 * docs/dominio/HORAS_COMPLEMENTARES.md).
 *
 * @property string $cac_id
 * @property string $registration_reg_id
 * @property string $complementary_category_ccg_id
 * @property int $cac_hours_claimed
 * @property ?int $cac_hours_granted reservado para a homologação — sempre null nesta entrega
 * @property ActivityStatus $cac_status
 */
#[ObservedBy([ComplementaryActivityObserver::class])]
final class ComplementaryActivity extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'complementary_activities';

    protected $primaryKey = 'cac_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'cac_created_at';

    public const UPDATED_AT = 'cac_updated_at';

    public const DELETED_AT = 'cac_deleted_at';

    protected $guarded = [];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_reg_id', 'reg_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(config('models.complementary_category'), 'complementary_category_ccg_id', 'ccg_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(config('models.user'), 'reviewer_usr_id', 'usr_id');
    }

    protected static function newFactory(): ComplementaryActivityFactory
    {
        return ComplementaryActivityFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'cac_status' => ActivityStatus::class,
            'cac_issued_at' => 'date',
        ];
    }
}
