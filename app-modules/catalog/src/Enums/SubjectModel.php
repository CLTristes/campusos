<?php

declare(strict_types=1);

namespace CampusOs\Catalog\Enums;

/**
 * "Modelo de disciplina" — a classificação do PPC. Os 8 valores observados na
 * matriz 45 da UTFPR (docs/dominio/DOCUMENTOS_ACADEMICOS.md §4.2).
 *
 * Confirma o achado central do catálogo: estágio, TCC, atividades
 * complementares e até o ENADE são LINHAS DA MATRIZ, não faixas de hora
 * paralelas. ATV001 tem 90 h; EST501 tem 450 h; as duas linhas de ENADE têm 0 h.
 */
enum SubjectModel: string
{
    case BasicScientific = 'basic_scientific';
    case Professional = 'professional';
    case Humanities = 'humanities';
    case Internship = 'internship';
    case ComplementaryActivities = 'complementary_activities';
    case Capstone = 'capstone';
    case EnadeEntry = 'enade_entry';
    case EnadeExit = 'enade_exit';

    /** Mapeia o texto impresso no documento. Usado pelo extrator e pelo seeder. */
    public static function fromDocument(string $printed): self
    {
        $n = mb_strtoupper(trim(preg_replace('/\s+/', ' ', $printed) ?? ''));

        return match (true) {
            str_contains($n, 'BÁSICA') => self::BasicScientific,
            str_contains($n, 'PROFISSIONAL') => self::Professional,
            str_contains($n, 'HUMANIDADES') => self::Humanities,
            str_contains($n, 'ESTÁGIO') => self::Internship,
            str_contains($n, 'COMPLEMENTAR') => self::ComplementaryActivities,
            str_contains($n, 'CONCLUSÃO') => self::Capstone,
            str_contains($n, 'INGRESSANTE') => self::EnadeEntry,
            str_contains($n, 'CONCLUINTE') => self::EnadeExit,
            default => throw new \ValueError("Modelo de disciplina desconhecido: {$printed}"),
        };
    }

    /** Disciplina de 0 hora que existe só como marco (ENADE). */
    public function isMilestone(): bool
    {
        return in_array($this, [self::EnadeEntry, self::EnadeExit], true);
    }
}
