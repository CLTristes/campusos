<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * curriculum_subjects — a disciplina DENTRO de uma matriz.
 *
 * A distinção mais importante do catálogo: `subjects` é a disciplina (global,
 * identidade única, onde o acervo se pendura); esta tabela é o LUGAR dela numa
 * matriz. A mesma Cálculo 1 é 3º período numa matriz e 2º em outra, continuando
 * a ser a mesma disciplina.
 *
 * `cbs_term` é o período SUGERIDO (a linha da grade impressa) — não onde o aluno
 * de fato cursou, que é subject_enrollments.term_trm_id. Confundir os dois é o
 * bug nº 1 de sistema acadêmico: quem atrasou cursa o "3º período" no 5º semestre.
 *
 * `elective_group_elg_id` preenchido ⇒ é optativa daquele conjunto (coluna [OPT]
 * do documento). Nulo ⇒ obrigatória.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_subjects', function (Blueprint $table): void {
            $table->uuid('cbs_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('curriculum_cur_id');
            $table->uuid('subject_sbj_id');
            $table->uuid('elective_group_elg_id')->nullable();
            $table->smallInteger('cbs_term');
            $table->string('cbs_nature', 16);
            $table->timestamp('cbs_created_at')->nullable();
            $table->timestamp('cbs_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('curriculum_cur_id')->references('cur_id')->on('curricula');
            $table->foreign('subject_sbj_id')->references('sbj_id')->on('subjects');
            $table->foreign('elective_group_elg_id')->references('elg_id')->on('elective_groups');
            $table->unique(['curriculum_cur_id', 'subject_sbj_id']);
            $table->index(['curriculum_cur_id', 'cbs_term']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_subjects');
    }
};
