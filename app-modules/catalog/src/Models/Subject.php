<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\SubjectFactory;
use CampusOs\Catalog\Enums\SubjectModel;
use CampusOs\Catalog\Observers\SubjectObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A disciplina — GLOBAL na instituição. É nesta tabela que o acervo do veterano
 * se pendura (ver o módulo lifeos), e é por ela ser global que a anotação
 * atravessa cursos.
 *
 * @property string $sbj_id
 * @property string $sbj_code
 * @property string $sbj_name
 * @property int $sbj_weekly_hours CHS
 * @property int $sbj_hours CHT
 * @property int $sbj_extension_hours CHEXT — contida no CHT, nunca somada
 */
#[ObservedBy([SubjectObserver::class])]
final class Subject extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'subjects';

    protected $primaryKey = 'sbj_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'sbj_created_at';

    public const UPDATED_AT = 'sbj_updated_at';

    public const DELETED_AT = 'sbj_deleted_at';

    protected $guarded = [];

    /** Carrega carga horária extensionista embutida? (PEX304, HSA003, HCH023…) */
    public function carriesExtension(): bool
    {
        return $this->sbj_extension_hours > 0;
    }

    protected static function newFactory(): SubjectFactory
    {
        return SubjectFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sbj_model' => SubjectModel::class,
            'sbj_workload_breakdown' => 'array',
        ];
    }
}
