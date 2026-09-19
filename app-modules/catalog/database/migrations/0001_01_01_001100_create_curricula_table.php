<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * curricula — a matriz curricular. A tabela que define o que "formar" significa.
 *
 * As colunas de hora são as regras de integralização viradas dado, e vêm do
 * bloco de fechamento do documento "Consulta Curso e Matriz Curricular"
 * (ver docs/dominio/DOCUMENTOS_ACADEMICOS.md §4.3). Para a matriz 45 da UTFPR:
 *
 *   CHTOBRIGATORIASMATRIZ 2730   CHEXTENSAO 300   TEMA_OBRIGARORIA 60
 *   CHTOPTATIVASMATRIZ     210   CHTOTALPPC 3000
 *
 * NÃO existe cur_total_hours de propósito: o total é
 * `mandatory + elective + standalone_extension` (2730 + 210 + 60 = 3000), e
 * duas fontes para o mesmo número sempre acabam discordando. A parcela
 * extensionista embutida nas disciplinas (240 h) NÃO soma — é verificada em
 * paralelo, somando sbj_extension_hours das aprovadas.
 *
 * Versionada: quem ingressou em 2023 se forma pela matriz vigente em 2023,
 * mesmo que o colegiado publique matriz nova depois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curricula', function (Blueprint $table): void {
            $table->uuid('cur_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('course_crs_id');
            $table->string('cur_code', 32);
            $table->string('cur_name');
            $table->smallInteger('cur_version')->default(1);
            $table->date('cur_effective_from')->nullable();
            $table->string('cur_status', 16)->default('active');

            // As três faixas que SOMAM o total a integralizar.
            $table->integer('cur_mandatory_hours')->default(0);
            $table->integer('cur_elective_hours')->default(0);
            $table->integer('cur_standalone_extension_hours')->default(0);

            // Exigência extensionista TOTAL — ortogonal, verificada em paralelo.
            $table->integer('cur_extension_hours')->default(0);

            $table->smallInteger('cur_expected_terms')->nullable();
            $table->smallInteger('cur_max_terms')->nullable();
            // Teto de carga horária por semestre (requerimento: "Carga horária
            // máxima permitida: 390").
            $table->integer('cur_max_term_hours')->nullable();
            // Déficit de CHS acumulado que impede avançar de período (16 na UTFPR).
            $table->smallInteger('cur_max_weekly_deficit')->nullable();

            $table->timestamp('cur_created_at')->nullable();
            $table->timestamp('cur_updated_at')->nullable();
            $table->timestamp('cur_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('course_crs_id')->references('crs_id')->on('courses');
            $table->unique(['course_crs_id', 'cur_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curricula');
    }
};
