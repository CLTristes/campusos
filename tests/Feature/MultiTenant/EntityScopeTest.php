<?php

declare(strict_types=1);

use CampusOs\Core\Scopes\EntityScope;
use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Isolamento de tenant — a garantia mais importante do sistema.
 *
 * Testado sobre Campus: é o primeiro model real com escopo de tenant, e o caso
 * é literal no domínio (o câmpus de uma universidade nunca pode aparecer para
 * outra). Substituiu o model de exemplo `Order`, removido no B0.
 */
uses(RefreshDatabase::class);

it('queries retornam apenas dados do tenant atual', function () {
    [$utfpr, $outra] = Entity::factory()->count(2)->create();

    TenantContext::set($utfpr->ent_id);
    Campus::factory()->create(['cps_code' => 'FB']);

    TenantContext::set($outra->ent_id);
    $campusDaOutra = Campus::factory()->create(['cps_code' => 'XX']);

    TenantContext::set($utfpr->ent_id);
    expect(Campus::query()->find($campusDaOutra->cps_id))->toBeNull()
        ->and(Campus::query()->count())->toBe(1);
});

it('create preenche entity_ent_id automaticamente a partir do contexto', function () {
    $entity = tenantContext();

    $campus = Campus::factory()->create();

    expect($campus->entity_ent_id)->toBe($entity->ent_id);
});

it('find por ID de outro tenant retorna null', function () {
    [$entityA, $entityB] = Entity::factory()->count(2)->create();

    TenantContext::set($entityA->ent_id);
    $campus = Campus::factory()->create();

    TenantContext::set($entityB->ent_id);
    expect(Campus::query()->find($campus->cps_id))->toBeNull();
});

it('withoutGlobalScope permite acesso administrativo deliberado a todos os tenants', function () {
    [$entityA, $entityB] = Entity::factory()->count(2)->create();

    TenantContext::set($entityA->ent_id);
    Campus::factory()->count(3)->create();

    TenantContext::set($entityB->ent_id);
    Campus::factory()->count(2)->create();

    expect(Campus::withoutGlobalScope(EntityScope::class)->count())->toBe(5);
});

it('withoutEntityScope executa o bloco sem filtro e restaura o contexto', function () {
    [$entityA, $entityB] = Entity::factory()->count(2)->create();

    TenantContext::set($entityA->ent_id);
    Campus::factory()->create();

    TenantContext::set($entityB->ent_id);
    Campus::factory()->create();

    $total = Campus::withoutEntityScope(fn (): int => Campus::query()->count());

    expect($total)->toBe(2)
        ->and(TenantContext::id())->toBe($entityB->ent_id);
});

it('o índice único de câmpus é por instituição, não global', function () {
    [$utfpr, $outra] = Entity::factory()->count(2)->create();

    TenantContext::set($utfpr->ent_id);
    Campus::factory()->create(['cps_code' => 'FB']);

    // A MESMA sigla numa instituição diferente não colide.
    TenantContext::set($outra->ent_id);
    $gemeo = Campus::factory()->create(['cps_code' => 'FB']);

    expect($gemeo->cps_code)->toBe('FB')
        ->and($gemeo->entity_ent_id)->toBe($outra->ent_id);
});
