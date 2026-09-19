<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\ComplementaryCategoryFactory;
use CampusOs\Catalog\Observers\ComplementaryCategoryObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * O teto de uma categoria de atividade complementar (B7) — dado mestre da
 * matriz, não fato de aluno.
 *
 * @property string $ccg_id
 * @property string $curriculum_cur_id
 * @property string $ccg_name
 * @property int $ccg_max_hours
 */
#[ObservedBy([ComplementaryCategoryObserver::class])]
final class ComplementaryCategory extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'complementary_categories';

    protected $primaryKey = 'ccg_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'ccg_created_at';

    public const UPDATED_AT = 'ccg_updated_at';

    protected $guarded = [];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_cur_id', 'cur_id');
    }

    protected static function newFactory(): ComplementaryCategoryFactory
    {
        return ComplementaryCategoryFactory::new();
    }
}
