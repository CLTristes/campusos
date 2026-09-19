<?php

declare(strict_types=1);

namespace Modules\Orders\Actions;

use Modules\Core\Actions\AbstractAction;
use Modules\Orders\Models\Order;

/**
 * [EXEMPLO — REMOVÍVEL] A Action de leitura canônica. Note o que NÃO está aqui:
 * nenhum `where('entity_ent_id', ...)` — o EntityScope injeta o filtro de tenant
 * automaticamente, então consultar o pedido de outro tenant devolve 404 por
 * construção (ModelNotFound → 404 no handler JSON do Laravel).
 */
final class GetOrderAction extends AbstractAction
{
    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'id' => ['required', 'string', 'uuid'],
        ];
    }

    protected function handle(array $data): mixed
    {
        return Order::query()->findOrFail($data['id']);
    }
}
