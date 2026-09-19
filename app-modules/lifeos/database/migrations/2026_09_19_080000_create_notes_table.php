<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * notes — o caderno do aluno, e o acervo do veterano no mesmo lugar.
 *
 * Nasce SEMPRE privada (`nte_visibility` default `private`) — publicar é ato
 * deliberado do autor, nunca automático. As três FKs de escopo
 * (`subject_sbj_id`, `offering_ofr_id`, `course_crs_id`) são todas nullable
 * porque cada nível de visibilidade usa uma delas ou nenhuma: uma nota
 * `institution` não precisa de disciplina nenhuma, uma `subject` precisa da
 * disciplina mas não da turma específica.
 *
 * `nte_term_trm_id` não filtra visibilidade nenhuma — é só o "quando foi
 * escrita" que aparece na tela ("Resumo da P2 — 2024/1"). O que faz o acervo
 * atravessar semestres é justamente NENHUMA dessas colunas ser usada como
 * filtro de termo na leitura (ver `NoteVisibilityScope`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table): void {
            $table->uuid('nte_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('author_usr_id');
            $table->uuid('subject_sbj_id')->nullable();
            $table->uuid('offering_ofr_id')->nullable();
            $table->uuid('course_crs_id')->nullable();
            $table->uuid('term_trm_id')->nullable();
            $table->string('nte_title');
            $table->text('nte_body_md');
            $table->string('nte_kind', 32);
            $table->string('nte_visibility', 32)->default('private');
            $table->timestamp('nte_published_at')->nullable();
            $table->timestamp('nte_created_at')->nullable();
            $table->timestamp('nte_updated_at')->nullable();
            $table->timestamp('nte_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('author_usr_id')->references('usr_id')->on('users');
            $table->foreign('subject_sbj_id')->references('sbj_id')->on('subjects');
            $table->foreign('offering_ofr_id')->references('ofr_id')->on('offerings');
            $table->foreign('course_crs_id')->references('crs_id')->on('courses');
            $table->foreign('term_trm_id')->references('trm_id')->on('terms');

            // A leitura mais comum: "o acervo desta disciplina" e "minhas notas".
            $table->index(['subject_sbj_id', 'nte_visibility']);
            $table->index(['author_usr_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
