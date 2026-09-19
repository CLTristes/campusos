<?php

declare(strict_types=1);

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\Entityable;
use Modules\Orders\Database\Factories\OrderFactory;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Observers\OrderObserver;

/**
 * [EXEMPLO — REMOVÍVEL] O agregado do módulo de exemplo, seguindo TODAS as
 * convenções de docs/arquitetura/BANCO.md:
 *
 *  - prefixo de 3 letras (`ord_`) em toda coluna;
 *  - PK UUID ($incrementing=false, $keyType=string, $primaryKey explícito);
 *  - constantes de timestamp prefixadas + SoftDeletes;
 *  - `use Entityable` (multi-tenant automático — filtro + preenchimento);
 *  - auditoria via #[ObservedBy] apontando para um observer que estende o
 *    AuditObserver do core.
 *
 * Este model NUNCA cruza a fronteira do módulo: para fora, saem OrderResource
 * (HTTP) ou OrderSummaryDTO (read model).
 *
 * @property string $ord_id
 * @property string $entity_ent_id
 * @property ?string $ord_ref
 * @property string $ord_customer_name
 * @property ?string $ord_customer_email
 * @property string $ord_amount
 * @property OrderStatus $ord_status
 * @property ?string $ord_failure_reason
 * @property ?string $ord_internal_notes
 * @property ?\Illuminate\Support\Carbon $ord_paid_at
 */
#[ObservedBy([OrderObserver::class])]
final class Order extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'orders';

    protected $primaryKey = 'ord_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'ord_created_at';

    public const UPDATED_AT = 'ord_updated_at';

    public const DELETED_AT = 'ord_deleted_at';

    protected $guarded = [];

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ord_amount' => 'decimal:2',
            'ord_status' => OrderStatus::class,
            'ord_paid_at' => 'datetime',
        ];
    }
}
