<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * subject_equivalences — "esta disciplina da matriz nova vale por aquela da antiga".
 *
 * É a origem do `Crédito Consignado` que aparece no histórico quando o aluno
 * muda de matriz (ver docs/dominio/DOCUMENTOS_ACADEMICOS.md §1.3). Sem esta
 * tabela, a importação do histórico não tem como saber que o `FSI103` cursado
 * em 2023 satisfaz o `FSI101` da matriz 45.
 *
 * `seq_code` é TEXTO, não FK para subjects: o equivalente quase sempre vem de
 * uma matriz antiga e não existe no catálogo atual da instituição. O que a
 * importação precisa é exatamente isso — casar um código impresso no documento.
 *
 * `seq_group` carrega a semântica que o documento explica no tooltip da coluna
 * ("Grupo indica se uma disciplina é equivalente a duas ou mais disciplinas"):
 *   NULL  → a equivalente sozinha satisfaz     (OR)
 *   N     → só o GRUPO N inteiro satisfaz      (AND)
 * Exemplo real: LIP201 ≡ AL32L + LP32L (grupo 1) — uma só não basta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_equivalences', function (Blueprint $table): void {
            $table->uuid('seq_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('curriculum_subject_cbs_id');
            $table->string('seq_code', 32);
            $table->integer('seq_hours')->default(0);
            $table->smallInteger('seq_group')->nullable();
            $table->timestamp('seq_created_at')->nullable();
            $table->timestamp('seq_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('curriculum_subject_cbs_id')->references('cbs_id')->on('curriculum_subjects');
            $table->unique(['curriculum_subject_cbs_id', 'seq_code'], 'subject_equivalences_unique');
            $table->index('seq_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_equivalences');
    }
};
