<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Lifeos\Database\Factories\NoteVoteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Um voto positivo numa nota — o fato bruto por trás de `notes.nte_upvotes_count`.
 * Sem `SoftDeletes` de propósito (ver a migração): desfazer o voto apaga a linha.
 *
 * @property string $nvt_id
 * @property string $note_nte_id
 * @property string $user_usr_id
 */
final class NoteVote extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'note_votes';

    protected $primaryKey = 'nvt_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'nvt_created_at';

    public const UPDATED_AT = 'nvt_updated_at';

    protected $guarded = [];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'note_nte_id', 'nte_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('models.user'), 'user_usr_id', 'usr_id');
    }

    protected static function newFactory(): NoteVoteFactory
    {
        return NoteVoteFactory::new();
    }
}
