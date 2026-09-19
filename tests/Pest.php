<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Pest — configuração da suíte
|--------------------------------------------------------------------------
|
| Todo teste em tests/Feature usa o TestCase do Laravel (container + HTTP).
| Testes de Unit que precisem do container declaram `uses(Tests\TestCase::class)`
| no próprio arquivo. O banco de teste é sqlite :memory: (phpunit.xml) e cada
| arquivo que toca o banco usa RefreshDatabase.
|
| Helpers globais de contexto de teste (o análogo do "apiContext" de um sistema
| real) moram aqui embaixo — mantenha-os pequenos e sem regra de negócio.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Monta o contexto mínimo multi-tenant dos testes: cria um tenant e o define
 * como contexto atual. Devolve a Entity criada.
 */
function tenantContext(): Modules\Tenancy\Models\Entity
{
    $entity = Modules\Tenancy\Models\Entity::factory()->create();

    Modules\Core\Tenancy\TenantContext::set($entity->ent_id);

    return $entity;
}
