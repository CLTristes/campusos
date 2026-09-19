<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O escopo do coordenador (B8 — painel da coordenação): qual curso ele
 * coordena. Nullable, mesmo padrão de `campus_cps_id` — nem todo usuário tem
 * um (aluno e `institution_admin` nunca preenchem; `institution_admin` enxerga
 * a instituição inteira via `EntityScope`, não precisa de curso nenhum).
 *
 * FK cruza para `catalog.courses` — permitido em migration (schema, não
 * import de classe PHP); a fronteira modular protege código, não FK de banco.
 *
 * Não suporta coordenador de mais de um curso — nenhuma coordenação
 * multi-curso existe no piloto (UTFPR/FB, curso 25). Se aparecer, vira tabela
 * pivot (`course_coordinators`) sem quebrar esta coluna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('course_crs_id')->nullable()->after('campus_cps_id');
            $table->foreign('course_crs_id')->references('crs_id')->on('courses');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['course_crs_id']);
            $table->dropColumn('course_crs_id');
        });
    }
};
