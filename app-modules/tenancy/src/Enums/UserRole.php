<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Enums;

/**
 * Os papéis do CampusOS. O RBAC granular por permissão (tabela `permissions`,
 * como no FibroMais) é ◇ planejado — hoje permissão É o papel.
 */
enum UserRole: string
{
    case Student = 'student';
    case Coordinator = 'coordinator';
    case Professor = 'professor';
    case InstitutionAdmin = 'institution_admin';

    /** Enxerga agregados da coordenação (nunca aluno nomeado). */
    public function seesInsights(): bool
    {
        return in_array($this, [self::Coordinator, self::InstitutionAdmin], true);
    }

    /** Implanta o catálogo: importa a matriz do curso na instituição. */
    public function managesCatalog(): bool
    {
        return in_array($this, [self::Coordinator, self::InstitutionAdmin], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Student => 'Estudante',
            self::Coordinator => 'Coordenação de curso',
            self::Professor => 'Docente',
            self::InstitutionAdmin => 'Gestão da instituição',
        };
    }
}
