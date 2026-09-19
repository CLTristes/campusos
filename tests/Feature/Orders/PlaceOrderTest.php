<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Jobs\ProcessOrderPaymentJob;
use Modules\Orders\Models\Order;

uses(RefreshDatabase::class);

it('POST /v1/orders responde 202 e despacha o job na fila nomeada', function () {
    Queue::fake();
    $entity = tenantContext();

    $response = $this->withHeader('X-Tenant-Id', $entity->ent_id)
        ->postJson('/v1/orders', [
            'ref' => 'pedido-001',
            'customer_name' => 'Maria Silva',
            'amount' => 150.5,
        ]);

    $response->assertStatus(202)
        ->assertJsonPath('data.status', OrderStatus::ProcessingPayment->value)
        ->assertJsonPath('data.ref', 'pedido-001');

    Queue::assertPushedOn('orders', ProcessOrderPaymentJob::class);

    expect(Order::query()->where('ord_ref', 'pedido-001')->exists())->toBeTrue();
});

it('é idempotente pela ref: repetir o POST não cria segundo pedido', function () {
    Queue::fake();
    $entity = tenantContext();

    $payload = ['ref' => 'pedido-002', 'customer_name' => 'Maria Silva', 'amount' => 90];

    $first = $this->withHeader('X-Tenant-Id', $entity->ent_id)->postJson('/v1/orders', $payload);
    $second = $this->withHeader('X-Tenant-Id', $entity->ent_id)->postJson('/v1/orders', $payload);

    expect($second->json('data.id'))->toBe($first->json('data.id'))
        ->and(Order::query()->where('ord_ref', 'pedido-002')->count())->toBe(1);

    Queue::assertPushed(ProcessOrderPaymentJob::class, 1);
});

it('valida a entrada e responde 422 sem tocar o banco', function () {
    $entity = tenantContext();

    $response = $this->withHeader('X-Tenant-Id', $entity->ent_id)
        ->postJson('/v1/orders', ['customer_name' => 'ab', 'amount' => -1]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['customer_name', 'amount']);

    expect(Order::query()->count())->toBe(0);
});

it('recusa requisição sem tenant resolvido (placeholder de auth)', function () {
    $this->postJson('/v1/orders', ['customer_name' => 'Maria', 'amount' => 10])
        ->assertStatus(401);
});

it('fluxo ponta a ponta (fila sync): pedido nasce, é cobrado e fica pago no banco', function () {
    // Sem Queue::fake: QUEUE_CONNECTION=sync roda o job inline no dispatch.
    $entity = tenantContext();

    $this->withHeader('X-Tenant-Id', $entity->ent_id)
        ->postJson('/v1/orders', ['customer_name' => 'Maria Silva', 'amount' => 100])
        ->assertStatus(202);

    $order = Order::query()->firstOrFail();

    expect($order->ord_status)->toBe(OrderStatus::Paid)
        ->and($order->ord_paid_at)->not->toBeNull();
});

it('GET /v1/orders/{id} devolve o pedido do tenant e 404 para o de outro tenant', function () {
    Queue::fake();
    $entityA = tenantContext();
    $order = Order::factory()->create();

    $this->withHeader('X-Tenant-Id', $entityA->ent_id)
        ->getJson("/v1/orders/{$order->ord_id}")
        ->assertOk()
        ->assertJsonPath('data.id', $order->ord_id);

    $entityB = tenantContext(); // outro tenant

    $this->withHeader('X-Tenant-Id', $entityB->ent_id)
        ->getJson("/v1/orders/{$order->ord_id}")
        ->assertNotFound();
});

it('nunca expõe colunas internas na resposta HTTP', function () {
    Queue::fake();
    $entity = tenantContext();
    $order = Order::factory()->create(['ord_internal_notes' => 'segredo']);

    $json = $this->withHeader('X-Tenant-Id', $entity->ent_id)
        ->getJson("/v1/orders/{$order->ord_id}")
        ->json('data');

    expect($json)->not->toHaveKey('ord_internal_notes')
        ->and(json_encode($json))->not->toContain('segredo');
});
