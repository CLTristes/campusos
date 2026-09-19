<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * subjects — a disciplina, GLOBAL na instituição (não por curso).
 *
 * É o que faz a nota do veterano de Engenharia aparecer para o calouro de
 * Química que cursa a mesma Cálculo 1 — se fosse por curso, o desafio 5.2
 * perderia metade do alcance de graça. É nesta tabela que o acervo se pendura.
 *
 * TRÊS colunas de hora, não uma (documento real, §4.1):
 *   sbj_weekly_hours  CHS   — carga SEMANAL; é dela, e só dela, que sai o
 *                             cálculo do período em que o aluno está
 *   sbj_hours         CHT   — carga total
 *   sbj_extension_hours CHEXT — parcela extensionista CONTIDA no CHT, nunca somada
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->uuid('sbj_id')->primary();
            $table->uuid('entity_ent_id');
            $table->string('sbj_code', 32);
            $table->string('sbj_name');
            $table->string('sbj_model', 48)->nullable();
            $table->smallInteger('sbj_weekly_hours')->default(0);
            $table->integer('sbj_hours')->default(0);
            $table->integer('sbj_extension_hours')->default(0);
            // Detalhe do PPC (teóricas, práticas, APS, APCC, AD, CHEAD). Zerado
            // na matriz 45, mas existe no documento — json em vez de 6 colunas
            // que ninguém consulta.
            $table->json('sbj_workload_breakdown')->nullable();
            $table->text('sbj_syllabus')->nullable();
            $table->timestamp('sbj_created_at')->nullable();
            $table->timestamp('sbj_updated_at')->nullable();
            $table->timestamp('sbj_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->unique(['entity_ent_id', 'sbj_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
