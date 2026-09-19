<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Models;

use CampusOs\Core\Models\Concerns\Entityable;
use CampusOs\Tenancy\Database\Factories\UserFactory;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Quem entra no sistema. Model de domínio com escopo de tenant e auditoria —
 * não o `App\Models\User` do esqueleto, que foi removido.
 *
 * @property string $usr_id
 * @property string $entity_ent_id
 * @property ?string $campus_cps_id
 * @property string $usr_name
 * @property string $usr_email
 * @property UserRole $usr_role
 * @property ?string $usr_registration_number
 */
#[ObservedBy([UserObserver::class])]
final class User extends Authenticatable
{
    use Entityable;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'users';

    protected $primaryKey = 'usr_id';

    public $incrementing = false;

    protected $keyType = 'string';

    public const CREATED_AT = 'usr_created_at';

    public const UPDATED_AT = 'usr_updated_at';

    public const DELETED_AT = 'usr_deleted_at';

    protected $guarded = [];

    /** Fora de toda serialização — o observer trata o diff de auditoria à parte. */
    protected $hidden = ['usr_password', 'remember_token'];

    /** A coluna de senha segue o prefixo da tabela, não o 'password' do framework. */
    public function getAuthPasswordName(): string
    {
        return 'usr_password';
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class, 'campus_cps_id', 'cps_id');
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'usr_role' => UserRole::class,
            'usr_password' => 'hashed',
            'usr_email_verified_at' => 'datetime',
            'usr_enabled_modules' => 'array',
        ];
    }
}
