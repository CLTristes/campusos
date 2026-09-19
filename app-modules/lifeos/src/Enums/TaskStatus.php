<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Enums;

/** As três colunas do quadro: nunca inventada por tela — o mesmo vocabulário em toda tarefa. */
enum TaskStatus: string
{
    case Todo = 'todo';
    case Doing = 'doing';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'A fazer',
            self::Doing => 'Em andamento',
            self::Done => 'Concluída',
        };
    }
}
