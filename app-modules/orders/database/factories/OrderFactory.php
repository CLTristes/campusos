<?php

declare(strict_types=1);

namespace Modules\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Order;

/**
 * [EXEMPLO — REMOVÍVEL] Factory do pedido. Não preenche entity_ent_id: nos testes,
 * defina o tenant com TenantContext::set() ANTES de criar (o Entityable preenche),
 * ou passe explicitamente — assim a factory não importa o módulo tenancy e a
 * fronteira fica limpa até nos test helpers.
 *
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'ord_customer_name' => fake()->name(),
            'ord_customer_email' => fake()->safeEmail(),
            'ord_amount' => fake()->randomFloat(2, 10, 500),
            'ord_status' => OrderStatus::ProcessingPayment,
        ];
    }
}
