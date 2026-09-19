<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * offerings — a TURMA: disciplina × semestre × código de turma × câmpus.
 *
 * É a granularidade do "meu colega deste semestre"; `subjects` é a do "veterano
 * de qualquer semestre". A diferença entre as duas é literalmente o desafio 5.2.
 *
 * `ofr_professor_name` é TEXTO, não FK para users: hoje nenhum docente tem conta,
 * e criar a tabela de docentes para guardar um nome seria over-engineering.
 *
 * `ofr_schedule` vem da grade de horários do requerimento de matrícula — json de
 * {dia, inicio, fim, sala}. É daqui que sai a geração automática das aulas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offerings', function (Blueprint $table): void {
            $table->uuid('ofr_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('term_trm_id');
            $table->uuid('subject_sbj_id');
            $table->uuid('campus_cps_id');
            $table->string('ofr_class_code', 32);
            $table->string('ofr_professor_name')->nullable();
            $table->json('ofr_schedule')->nullable();
            $table->smallInteger('ofr_seats')->nullable();
            $table->timestamp('ofr_created_at')->nullable();
            $table->timestamp('ofr_updated_at')->nullable();
            $table->timestamp('ofr_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('term_trm_id')->references('trm_id')->on('terms');
            $table->foreign('subject_sbj_id')->references('sbj_id')->on('subjects');
            $table->foreign('campus_cps_id')->references('cps_id')->on('campuses');
            $table->unique(['term_trm_id', 'subject_sbj_id', 'ofr_class_code', 'campus_cps_id'], 'offerings_turma_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offerings');
    }
};
