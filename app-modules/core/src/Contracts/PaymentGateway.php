<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\DTOs\PaymentResult;

/**
 * [EXEMPLO — REMOVÍVEL] Contrato de fronteira do fluxo de exemplo orders→payments.
 *
 * Demonstra o mecanismo nº 1 de comunicação entre módulos (docs/arquitetura/
 * COMUNICACAO.md): o módulo `orders` precisa da capacidade "cobrar", que mora no
 * módulo `payments`. Ele NUNCA importa a classe concreta — depende só desta
 * interface, e o PaymentsServiceProvider faz o bind da implementação.
 *
 * Ao iniciar um sistema real (skill /prontuario), remova este arquivo junto com
 * os módulos de exemplo e crie os SEUS contratos seguindo exatamente este molde.
 */
interface PaymentGateway
{
    /**
     * Cobra o valor e devolve o desfecho (aprovado ou recusado DE NEGÓCIO).
     *
     * Erros de transporte (timeout, 5xx, indisponibilidade) LANÇAM exceção — nunca
     * viram retorno. É a regra de ouro nº 7: quem chama de dentro de um Job deixa a
     * exceção subir para o retry da fila; recusa de negócio vira estado final.
     */
    public function charge(string $reference, float $amount): PaymentResult;
}
