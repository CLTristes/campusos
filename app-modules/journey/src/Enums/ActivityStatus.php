<?php

declare(strict_types=1);

namespace CampusOs\Journey\Enums;

/**
 * O ciclo de uma atividade complementar (B7): rascunho, enviado, aprovado,
 * rejeitado. A fila de homologação da coordenação é ◇ planejado — hoje o
 * aluno declara e o contador anda, com a ressalva na tela.
 */
enum ActivityStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * HOJE: Draft e Submitted também contam (declaratório, com ressalva).
     * COM a fila de homologação: só Approved. Um ponto único de troca — é
     * para isso que a pergunta mora no enum, e não espalhada em read models.
     */
    public function countsTowardHours(): bool
    {
        return $this !== self::Rejected;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Submitted => 'Enviada',
            self::Approved => 'Aprovada',
            self::Rejected => 'Rejeitada',
        };
    }
}
