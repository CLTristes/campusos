<?php

declare(strict_types=1);

namespace Modules\Orders\Actions;

use Modules\Core\Actions\AbstractAction;
use Modules\Core\Tenancy\TenantContext;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Jobs\ProcessOrderPaymentJob;
use Modules\Orders\Models\Order;

/**
 * [EXEMPLO — REMOVÍVEL] A Action de escrita canônica do template. Tudo que um
 * caso de uso assíncrono precisa está aqui, na ordem certa:
 *
 *  1. sanitize/rules/authorize herdados do template method (AbstractAction);
 *  2. idempotência pela `ref` do cliente — repetir o POST com a mesma ref NÃO
 *     cria segundo pedido, devolve o existente;
 *  3. persiste o agregado em estado "processando" (Entityable preenche o tenant);
 *  4. despacha o Job pesado para a fila nomeada e retorna IMEDIATAMENTE — o
 *     Controller traduz isso num HTTP 202.
 *
 * A Action é interna ao módulo (sem interface no core) e é a MESMA para qualquer
 * canal de entrada (REST hoje; MCP/CLI amanhã, cada um com seu Input adapter).
 */
final class PlaceOrderAction extends AbstractAction
{
    protected function sanitize(array $input): array
    {
        if (isset($input['customer_name'])) {
            $input['customer_name'] = trim((string) $input['customer_name']);
        }

        return $input;
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'ref' => ['sometimes', 'nullable', 'string', 'max:100'],
            'customer_name' => ['required', 'string', 'min:3', 'max:255'],
            'customer_email' => ['sometimes', 'nullable', 'email'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    protected function authorize(array $data): void
    {
        // Escrita exige tenant no contexto — sem isso a coluna NOT NULL barraria
        // de qualquer forma, mas falhar aqui dá um erro claro (403) em vez de 500.
        abort_unless(TenantContext::has(), 403, 'Contexto de tenant ausente.');
    }

    protected function handle(array $data): mixed
    {
        // Idempotência: mesma ref (dentro do tenant — EntityScope filtra) = mesmo pedido.
        if (($data['ref'] ?? null) !== null) {
            $existing = Order::query()->where('ord_ref', $data['ref'])->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        $order = Order::query()->create([
            'ord_ref' => $data['ref'] ?? null,
            'ord_customer_name' => $data['customer_name'],
            'ord_customer_email' => $data['customer_email'] ?? null,
            'ord_amount' => $data['amount'],
            'ord_status' => OrderStatus::ProcessingPayment,
        ]);

        // O tenant viaja EXPLICITAMENTE com o job: o worker não tem sessão HTTP e
        // precisa restaurar o contexto via TenantContext::runAs() (regra de ouro nº 4).
        ProcessOrderPaymentJob::dispatch($order->ord_id, (string) TenantContext::id())
            ->onQueue('orders');

        return $order;
    }
}
