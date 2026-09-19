<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Lifeos\Database\Factories\NoteFactory;
use CampusOs\Lifeos\Enums\NoteKind;
use CampusOs\Lifeos\Enums\Visibility;
use CampusOs\Lifeos\Scopes\NoteVisibilityScope;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * O caderno do aluno. Nasce privada sempre — publicar é ato deliberado do
 * autor (`UpdateNoteVisibilityAction`), nunca automático.
 *
 * @property string $nte_id
 * @property string $author_usr_id
 * @property ?string $subject_sbj_id
 * @property ?string $offering_ofr_id
 * @property ?string $course_crs_id
 * @property Visibility $nte_visibility
 * @property NoteKind $nte_kind
 */
final class Note extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'notes';

    protected $primaryKey = 'nte_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'nte_created_at';

    public const UPDATED_AT = 'nte_updated_at';

    public const DELETED_AT = 'nte_deleted_at';

    protected $guarded = [];

    protected static function booted(): void
    {
        self::addGlobalScope(new NoteVisibilityScope);
    }

    /**
     * Consulta explícita, independente de quem está autenticado no momento —
     * é o que permite `Note::visibleTo($outroUsuario)` em teste ou em job.
     *
     * Remove o global scope antes: ele resolve por `Auth::user()`, e sem
     * request autenticada (aqui, ou em CLI/job) devolveria "sempre falso" por
     * cima do filtro explícito que estamos justamente fornecendo.
     */
    public function scopeVisibleTo(Builder $query, Authenticatable $user): Builder
    {
        return NoteVisibilityScope::constrain(
            $query->withoutGlobalScope(NoteVisibilityScope::class),
            $user,
        );
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('models.user'), 'author_usr_id', 'usr_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(config('models.subject'), 'subject_sbj_id', 'sbj_id');
    }

    public function offering(): BelongsTo
    {
        return $this->belongsTo(config('models.offering'), 'offering_ofr_id', 'ofr_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(config('models.course'), 'course_crs_id', 'crs_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(config('models.term'), 'term_trm_id', 'trm_id');
    }

    protected static function newFactory(): NoteFactory
    {
        return NoteFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'nte_visibility' => Visibility::class,
            'nte_kind' => NoteKind::class,
            'nte_published_at' => 'datetime',
        ];
    }
}
