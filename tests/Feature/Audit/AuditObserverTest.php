<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\AuditLog;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
});

it('cria registro em audit_logs ao criar um pedido', function () {
    $order = Order::factory()->create();

    $audit = AuditLog::query()
        ->where('aud_table', 'orders')
        ->where('aud_record_id', $order->ord_id)
        ->where('aud_action', 'created')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->aud_before)->toBeNull()
        ->and($audit->aud_after)->not->toBeNull()
        ->and($audit->entity_ent_id)->toBe($order->entity_ent_id);
});

it('registra o diff real (antes/depois) em updated', function () {
    $order = Order::factory()->create(['ord_customer_name' => 'Cliente Velho']);

    $order->update(['ord_customer_name' => 'Cliente Novo']);

    $audit = AuditLog::query()
        ->where('aud_table', 'orders')
        ->where('aud_action', 'updated')
        ->latest('aud_created_at')
        ->first();

    expect($audit->aud_before['ord_customer_name'])->toBe('Cliente Velho')
        ->and($audit->aud_after['ord_customer_name'])->toBe('Cliente Novo');
});

it('não expõe colunas sensíveis ($hidden do observer) no diff', function () {
    $order = Order::factory()->create(['ord_internal_notes' => 'segredo interno']);

    $audit = AuditLog::query()
        ->where('aud_table', 'orders')
        ->where('aud_record_id', $order->ord_id)
        ->where('aud_action', 'created')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->aud_after)->not->toHaveKey('ord_internal_notes');
});

it('registra deleted com o estado anterior', function () {
    $order = Order::factory()->create();

    $order->delete();

    $audit = AuditLog::query()
        ->where('aud_table', 'orders')
        ->where('aud_record_id', $order->ord_id)
        ->where('aud_action', 'deleted')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->aud_before)->not->toBeNull()
        ->and($audit->aud_after)->toBeNull();
});

it('update sem mudança real não gera audit_log', function () {
    $order = Order::factory()->create();
    $before = AuditLog::query()->count();

    $order->update(['ord_status' => $order->ord_status]);

    expect(AuditLog::query()->count())->toBe($before);
});

it('audit_logs registra mutações de todos os tenants (sem EntityScope)', function () {
    Order::factory()->create();

    $other = tenantContext(); // troca o tenant do contexto
    Order::factory()->create();

    $tenants = AuditLog::query()
        ->where('aud_table', 'orders')
        ->pluck('entity_ent_id')
        ->unique();

    expect($tenants)->toHaveCount(2);
});

// A máquina de estados fica legível na trilha: created → updated(paid).
it('conta a história completa do registro em ordem cronológica', function () {
    $order = Order::factory()->create();
    $order->update(['ord_status' => OrderStatus::Paid, 'ord_paid_at' => now()]);

    $trail = AuditLog::query()
        ->where('aud_table', 'orders')
        ->where('aud_record_id', $order->ord_id)
        ->orderBy('aud_created_at')
        ->pluck('aud_action');

    expect($trail->all())->toBe(['created', 'updated']);
});
