<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Enums;

/**
 * A escada de visibilidade — cinco degraus, um vocabulário só. Mesmo enum
 * serve `notes` e, no futuro, `tasks`/`events`: copiar um "quase igual" por
 * tabela é como o LifeOS original acabou com "Em Andamento" e "Em Progresso"
 * como a mesma coisa em tabelas diferentes.
 */
enum Visibility: string
{
    case Private = 'private';
    case Offering = 'offering';
    case Subject = 'subject';
    case Course = 'course';
    case Institution = 'institution';

    /** Precisa saber a TURMA específica — mais restrito que a disciplina. */
    public function requiresOffering(): bool
    {
        return $this === self::Offering;
    }

    /** Precisa saber a disciplina (a turma sozinha já implica a disciplina). */
    public function requiresSubject(): bool
    {
        return in_array($this, [self::Offering, self::Subject], true);
    }

    public function requiresCourse(): bool
    {
        return $this === self::Course;
    }

    /** Atravessa semestres? A pergunta que define o desafio 5.2. */
    public function crossesTerms(): bool
    {
        return in_array($this, [self::Subject, self::Course, self::Institution], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Private => 'Privada',
            self::Offering => 'Minha turma',
            self::Subject => 'Quem já cursou esta disciplina',
            self::Course => 'Todo o curso',
            self::Institution => 'Toda a instituição',
        };
    }
}
