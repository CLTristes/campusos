<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Tenancy\Database\Factories\CampusFactory;
use CampusOs\Tenancy\Observers\CampusObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Câmpus da instituição — recorte interno, não tenant.
 *
 * @property string $cps_id
 * @property string $entity_ent_id
 * @property string $cps_name
 * @property string $cps_code
 * @property ?string $cps_city
 */
#[ObservedBy([CampusObserver::class])]
final class Campus extends Model
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'campuses';

    protected $primaryKey = 'cps_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'cps_created_at';

    public const UPDATED_AT = 'cps_updated_at';

    public const DELETED_AT = 'cps_deleted_at';

    protected $guarded = [];

    protected static function newFactory(): CampusFactory
    {
        return CampusFactory::new();
    }
}
