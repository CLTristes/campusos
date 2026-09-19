<?php

declare(strict_types=1);

namespace CampusOs\Core\Scopes;

use CampusOs\Core\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope que filtra todo model multi-tenant pelo entity_id do contexto atual.
 *
 * Em contexto sem tenant (CLI/seeder/worker antes de restaurar o contexto) o
 * filtro NÃO é aplicado — para acesso administrativo deliberado use
 * Model::withoutGlobalScope(EntityScope::class) ou TenantContext::withoutScope().
 */
final class EntityScope implements Scope
{
    /** Coluna de tenant presente em todo model que usa Entityable. */
    public const COLUMN = 'entity_ent_id';

    public function apply(Builder $builder, Model $model): void
    {
        $entityId = TenantContext::id();

        if ($entityId === null) {
            return;
        }

        // Qualificado com a tabela para evitar ambiguidade em joins.
        $builder->where($model->getTable().'.'.self::COLUMN, $entityId);
    }
}
