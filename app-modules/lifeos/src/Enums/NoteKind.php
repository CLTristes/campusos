<?php

declare(strict_types=1);

namespace CampusOs\Lifeos\Enums;

/** Cinco valores, filtráveis — o calouro que quer a lista resolvida não deveria ler resumos. */
enum NoteKind: string
{
    case Summary = 'summary';
    case PastExam = 'past_exam';
    case SolvedExerciseList = 'solved_exercise_list';
    case Material = 'material';
    case Tip = 'tip';

    public function label(): string
    {
        return match ($this) {
            self::Summary => 'Resumo',
            self::PastExam => 'Prova antiga',
            self::SolvedExerciseList => 'Lista resolvida',
            self::Material => 'Material',
            self::Tip => 'Dica',
        };
    }
}
