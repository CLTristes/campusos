<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Models;

use CampusOs\Catalog\Database\Factories\TermFactory;
use CampusOs\Catalog\Enums\TermStatus;
use CampusOs\Catalog\Observers\TermObserver;
use CampusOs\Core\Models\Concerns\Entityable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * O semestre letivo.
 *
 * @property int $trm_year
 * @property int $trm_period
 */
#[ObservedBy([TermObserver::class])]
final class Term extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;

    protected $table = 'terms';

    protected $primaryKey = 'trm_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'trm_created_at';

    public const UPDATED_AT = 'trm_updated_at';

    protected $guarded = [];

    /** "2026/1" — como a universidade escreve, e como o documento imprime. */
    public function label(): string
    {
        return "{$this->trm_year}/{$this->trm_period}";
    }

    /** Ordem cronológica em número — 2026/1 => 20261. Útil em comparação. */
    public function sortKey(): int
    {
        return ($this->trm_year * 10) + $this->trm_period;
    }

    protected static function newFactory(): TermFactory
    {
        return TermFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'trm_status' => TermStatus::class,
            'trm_starts_at' => 'date',
            'trm_ends_at' => 'date',
        ];
    }
}
