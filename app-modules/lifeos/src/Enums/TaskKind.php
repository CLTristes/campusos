<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Enums;

/** Quatro naturezas — a agenda separa "prova" de "leitura" sem o aluno precisar ler o título inteiro. */
enum TaskKind: string
{
    case Exam = 'exam';
    case Assignment = 'assignment';
    case Reading = 'reading';
    case Personal = 'personal';

    public function label(): string
    {
        return match ($this) {
            self::Exam => 'Prova',
            self::Assignment => 'Trabalho',
            self::Reading => 'Leitura',
            self::Personal => 'Pessoal',
        };
    }
}
