<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Contracts\OrderReadModel;
use Modules\Core\DTOs\OrderSummaryDTO;
use Modules\Orders\Models\Order;

uses(RefreshDatabase::class);

it('devolve um DTO imutável através da fronteira — nunca o model', function () {
    tenantContext();
    $order = Order::factory()->create(['ord_ref' => 'pedido-rm', 'ord_amount' => 42.5]);

    // Resolve pela INTERFACE do core: quem consome não conhece a implementação.
    $summary = app(OrderReadModel::class)->summaryById($order->ord_id);

    expect($summary)->toBeInstanceOf(OrderSummaryDTO::class)
        ->and($summary->ref)->toBe('pedido-rm')
        ->and($summary->amount)->toBe(42.5)
        ->and($summary->status)->toBe($order->ord_status->value);
});

it('devolve null para pedido inexistente', function () {
    tenantContext();

    expect(app(OrderReadModel::class)->summaryById('00000000-0000-0000-0000-000000000000'))
        ->toBeNull();
});

it('respeita o tenant do contexto (EntityScope vale no read model)', function () {
    tenantContext();
    $order = Order::factory()->create();

    tenantContext(); // troca para outro tenant

    expect(app(OrderReadModel::class)->summaryById($order->ord_id))->toBeNull();
});
