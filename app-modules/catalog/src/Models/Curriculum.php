<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\CurriculumFactory;
use CampusOs\Catalog\Enums\CurriculumStatus;
use CampusOs\Catalog\Observers\CurriculumObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A matriz curricular — a tabela que define o que "formar" significa.
 *
 * @property string $cur_id
 * @property string $cur_code
 * @property int $cur_mandatory_hours
 * @property int $cur_elective_hours
 * @property int $cur_standalone_extension_hours
 * @property int $cur_extension_hours
 */
#[ObservedBy([CurriculumObserver::class])]
final class Curriculum extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'curricula';

    protected $primaryKey = 'cur_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'cur_created_at';

    public const UPDATED_AT = 'cur_updated_at';

    public const DELETED_AT = 'cur_deleted_at';

    protected $guarded = [];

    /**
     * O total a integralizar. NUNCA uma coluna: duas fontes para o mesmo número
     * sempre acabam discordando.
     *
     * A fórmula é a do próprio documento da matriz (§4.3):
     *   CHTOTALPPC := SomaCHSemExt + (CHEXTENSAO - chext_discObrigatorias)
     *
     * O segundo termo é `cur_standalone_extension_hours` — a parcela de extensão
     * que NÃO cabe dentro de disciplina e por isso soma. A extensão embutida nas
     * disciplinas (240 h na matriz 45) não entra aqui: é verificada em paralelo.
     *
     * Matriz 45: 2730 + 210 + 60 = 3000.
     */
    public function totalRequiredHours(): int
    {
        return $this->cur_mandatory_hours
            + $this->cur_elective_hours
            + $this->cur_standalone_extension_hours;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_crs_id', 'crs_id');
    }

    public function curriculumSubjects(): HasMany
    {
        return $this->hasMany(CurriculumSubject::class, 'curriculum_cur_id', 'cur_id');
    }

    public function electiveGroups(): HasMany
    {
        return $this->hasMany(ElectiveGroup::class, 'curriculum_cur_id', 'cur_id');
    }

    /** Os tetos por categoria de atividade complementar (B7) — dado mestre. */
    public function complementaryCategories(): HasMany
    {
        return $this->hasMany(ComplementaryCategory::class, 'curriculum_cur_id', 'cur_id');
    }

    protected static function newFactory(): CurriculumFactory
    {
        return CurriculumFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'cur_status' => CurriculumStatus::class,
            'cur_effective_from' => 'date',
        ];
    }
}
