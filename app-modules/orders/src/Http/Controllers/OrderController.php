<?php

declare(strict_types=1);

namespace Modules\Orders\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Actions\Input\HttpInputAdapter;
use Modules\Orders\Actions\GetOrderAction;
use Modules\Orders\Actions\PlaceOrderAction;
use Modules\Orders\Http\Resources\OrderResource;

/**
 * [EXEMPLO — REMOVÍVEL] Controller-adapter FINO: traduz HTTP ⇄ Action e nada mais.
 * Nenhuma regra de negócio mora aqui — se você está escrevendo um `if` de domínio
 * num Controller, ele pertence à Action. A Action chega por method injection
 * (o container resolve o type-hint e monta a árvore de dependências inteira).
 */
final class OrderController
{
    /**
     * POST /v1/orders — cria o pedido e devolve 202 Accepted: o processamento
     * (cobrança) é assíncrono; o cliente acompanha pelo GET.
     */
    public function store(Request $request, PlaceOrderAction $action): JsonResponse
    {
        $order = $action->execute(HttpInputAdapter::from($request));

        return (new OrderResource($order))
            ->response($request)
            ->setStatusCode(202);
    }

    /** GET /v1/orders/{id} — consulta o pedido (do tenant atual, só dele). */
    public function show(Request $request, string $id, GetOrderAction $action): OrderResource
    {
        $order = $action->execute(HttpInputAdapter::from($request, ['id' => $id]));

        return new OrderResource($order);
    }
}
