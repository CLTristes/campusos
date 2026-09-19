<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Pest — configuração da suíte
|--------------------------------------------------------------------------
|
| Todo teste em tests/Feature E todo teste dentro de um módulo
| (app-modules/x/tests) usa o TestCase do Laravel (container + HTTP).
| Testes de Unit que precisem do container declaram `uses(Tests\TestCase::class)`
| no próprio arquivo. O banco de teste é sqlite :memory: (phpunit.xml) e cada
| arquivo que toca o banco usa RefreshDatabase.
|
| Helpers globais de contexto de teste (o análogo do "apiContext" de um sistema
| real) moram aqui embaixo — mantenha-os pequenos e sem regra de negócio.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Feature', '../app-modules');

// O `->in('../app-modules')` não é detalhe. O phpunit.xml já declarava a suíte
// "Modules" apontando para os diretórios de teste dentro de cada módulo, mas sem
// esta linha o Pest não estende o TestCase lá — e TODO teste de módulo morre em
// "Call to a member function connection() on null", erro que não diz nada sobre a
// causa. Descoberto ao escrever o primeiro teste dentro de um módulo; o template
// de origem tem a mesma lacuna.

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Monta o contexto mínimo multi-tenant dos testes: cria um tenant e o define
 * como contexto atual. Devolve a Entity criada.
 */
function tenantContext(): CampusOs\Tenancy\Models\Entity
{
    $entity = CampusOs\Tenancy\Models\Entity::factory()->create();

    CampusOs\Core\Tenancy\TenantContext::set($entity->ent_id);

    return $entity;
}
