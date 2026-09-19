<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Lifeos\Database\Factories\TaskFactory;
use CampusOs\Lifeos\Enums\TaskKind;
use CampusOs\Lifeos\Enums\TaskStatus;
use CampusOs\Lifeos\Enums\Visibility;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * O organizador pessoal do aluno, e a tarefa da turma (B6) no mesmo lugar. Ao
 * contrário de `Note`, não nasce sempre privada — cadastrar já em `offering`
 * é o próprio ato de compartilhar com a turma (`CreateTaskAction`).
 *
 * `tsk_visibility` é o MESMO enum `Visibility` do acervo de notas — de
 * propósito, para não repetir a armadilha do LifeOS original ("Em Andamento"
 * × "Em Progresso" como a mesma coisa em tabelas diferentes).
 *
 * @property string $tsk_id
 * @property string $owner_usr_id
 * @property ?string $subject_sbj_id
 * @property ?string $offering_ofr_id
 * @property ?string $origin_tsk_id
 * @property TaskStatus $tsk_status
 * @property TaskKind $tsk_kind
 * @property Visibility $tsk_visibility
 */
final class Task extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'tasks';

    protected $primaryKey = 'tsk_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'tsk_created_at';

    public const UPDATED_AT = 'tsk_updated_at';

    public const DELETED_AT = 'tsk_deleted_at';

    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(config('models.user'), 'owner_usr_id', 'usr_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(config('models.subject'), 'subject_sbj_id', 'sbj_id');
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(config('models.offering'), 'offering_ofr_id', 'ofr_id');
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(self::class, 'origin_tsk_id', 'tsk_id');
    }

    /** As cópias que outros alunos adotaram A PARTIR desta tarefa. */
    public function adoptions(): HasMany
    {
        return $this->hasMany(self::class, 'origin_tsk_id', 'tsk_id');
    }

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tsk_status' => TaskStatus::class,
            'tsk_kind' => TaskKind::class,
            'tsk_visibility' => Visibility::class,
            'tsk_due_at' => 'datetime',
        ];
    }
}
