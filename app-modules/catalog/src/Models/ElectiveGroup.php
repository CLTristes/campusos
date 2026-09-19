<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\ElectiveGroupFactory;
use CampusOs\Catalog\Observers\ElectiveGroupObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * O conjunto de optativas da matriz — [941] na matriz 45.
 *
 * @property string $elg_id
 * @property string $elg_code
 * @property int $elg_required_hours
 */
#[ObservedBy([ElectiveGroupObserver::class])]
final class ElectiveGroup extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'elective_groups';

    protected $primaryKey = 'elg_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'elg_created_at';

    public const UPDATED_AT = 'elg_updated_at';

    protected $guarded = [];

    /**
     * CHS que o conjunto ocupa em CADA período em que se distribui.
     *
     * O documento avisa que pode dar número não inteiro — 14 / 4 = 3,5 na matriz
     * 45 — e o quadro de cálculo do período do histórico usa exatamente 3,50.
     */
    public function weeklyHoursPerTerm(): float
    {
        $terms = ($this->elg_last_term - $this->elg_first_term) + 1;

        return $terms > 0 ? (float) $this->elg_weekly_hours / $terms : 0.0;
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_cur_id', 'cur_id');
    }

    public function curriculumSubjects(): HasMany
    {
        return $this->hasMany(CurriculumSubject::class, 'elective_group_elg_id', 'elg_id');
    }

    protected static function newFactory(): ElectiveGroupFactory
    {
        return ElectiveGroupFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['elg_weekly_hours' => 'decimal:2'];
    }
}
