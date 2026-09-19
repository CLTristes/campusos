<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\Events\OrderPaid;
use Modules\Core\Exceptions\PaymentGatewayUnavailableException;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Jobs\ProcessOrderPaymentJob;
use Modules\Orders\Models\Order;
use Modules\Payments\Gateways\FakePaymentGateway;

uses(RefreshDatabase::class);

function runJobFor(Order $order): void
{
    $job = new ProcessOrderPaymentJob($order->ord_id, (string) $order->entity_ent_id);
    $job->handle(app(PaymentGateway::class));
}

it('pagamento aprovado → paid + evento OrderPaid publicado', function () {
    Event::fake([OrderPaid::class]);
    tenantContext();
    $order = Order::factory()->create(['ord_amount' => 100]);

    runJobFor($order);

    $order->refresh();

    expect($order->ord_status)->toBe(OrderStatus::Paid)
        ->and($order->ord_paid_at)->not->toBeNull();

    Event::assertDispatched(
        OrderPaid::class,
        fn (OrderPaid $e): bool => $e->orderId === $order->ord_id
            && $e->entityId === $order->entity_ent_id,
    );
});

it('recusa DE NEGÓCIO → estado final payment_failed, sem exceção e sem evento', function () {
    Event::fake([OrderPaid::class]);
    tenantContext();
    $order = Order::factory()->create(['ord_amount' => FakePaymentGateway::DECLINE_AMOUNT]);

    runJobFor($order);

    $order->refresh();

    expect($order->ord_status)->toBe(OrderStatus::PaymentFailed)
        ->and($order->ord_failure_reason)->toBe('insufficient_funds');

    Event::assertNotDispatched(OrderPaid::class);
});

it('erro de TRANSPORTE → a exceção SOBE (a fila reprocessa) e o pedido segue em processamento', function () {
    tenantContext();
    $order = Order::factory()->create(['ord_amount' => FakePaymentGateway::UNAVAILABLE_AMOUNT]);

    expect(fn () => runJobFor($order))
        ->toThrow(PaymentGatewayUnavailableException::class);

    expect($order->refresh()->ord_status)->toBe(OrderStatus::ProcessingPayment);
});

it('é idempotente: reentrega do job após sucesso não reprocessa', function () {
    Event::fake([OrderPaid::class]);
    tenantContext();
    $order = Order::factory()->create([
        'ord_amount' => 100,
        'ord_status' => OrderStatus::Paid,
        'ord_paid_at' => now()->subMinute(),
    ]);
    $paidAt = $order->ord_paid_at;

    runJobFor($order);

    expect($order->refresh()->ord_paid_at->equalTo($paidAt))->toBeTrue();
    Event::assertNotDispatched(OrderPaid::class);
});

it('restaura o contexto do tenant dentro do worker (regra de ouro nº 4)', function () {
    $entity = tenantContext();
    $order = Order::factory()->create(['ord_amount' => 100]);

    // Simula o worker: nenhum tenant no contexto antes de o job rodar.
    Modules\Core\Tenancy\TenantContext::forget();

    runJobFor($order);

    // O job achou o pedido (runAs restaurou o escopo) e finalizou sem vazar contexto.
    expect($order->refresh()->ord_status)->toBe(OrderStatus::Paid)
        ->and(Modules\Core\Tenancy\TenantContext::has())->toBeFalse();
});
