<?php

declare(strict_types=1);

namespace Modules\Core\DTOs;

/**
 * [EXEMPLO — REMOVÍVEL] DTO devolvido pelo read model OrderReadModel.
 *
 * O model Eloquent `Order` nunca cruza a fronteira do módulo `orders` — quem
 * precisa ler um pedido de fora recebe este objeto imutável. DTO ≠ Resource:
 * o Resource serializa o model em JSON para o cliente HTTP (e vive dentro do
 * módulo dono); o DTO cruza a fronteira ENTRE módulos.
 */
final class OrderSummaryDTO
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $ref,
        public readonly string $status,
        public readonly float $amount,
    ) {}
}
