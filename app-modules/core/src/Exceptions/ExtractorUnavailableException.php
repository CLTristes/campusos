<?php

declare(strict_types=1);

namespace CampusOs\Core\Exceptions;

use RuntimeException;

/**
 * Erro de TRANSPORTE ao falar com o extrator — timeout, 5xx, rate limit,
 * credencial inválida.
 *
 * Regra de ouro nº 7: erro de transporte LANÇA e a fila reprocessa; recusa de
 * negócio vira estado final. Capturar isto dentro do Job transformaria uma
 * indisponibilidade momentânea num documento marcado como ilegível para sempre.
 */
final class ExtractorUnavailableException extends RuntimeException {}
