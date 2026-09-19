<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * prerequisites — as arestas do grafo de dependência.
 *
 * Pré-requisito é DA MATRIZ, não da disciplina: a mesma Cálculo 2 pode exigir
 * coisas diferentes em cursos diferentes. Por isso aponta para
 * curriculum_subjects nos dois lados, nunca para subjects.
 *
 * `required_cbs_id` é nullable porque nem todo pré-requisito aponta para
 * disciplina — confirmado na matriz 45: EST501 (Estágio Curricular Obrigatório)
 * exige "Período: 5", um período mínimo. Ver docs/dominio/DOCUMENTOS_ACADEMICOS.md §4.4.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prerequisites', function (Blueprint $table): void {
            $table->uuid('prq_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('curriculum_subject_cbs_id');
            $table->uuid('required_cbs_id')->nullable();
            $table->string('prq_type', 24);
            $table->smallInteger('prq_min_term')->nullable();
            $table->integer('prq_min_hours')->nullable();
            $table->timestamp('prq_created_at')->nullable();
            $table->timestamp('prq_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('curriculum_subject_cbs_id')->references('cbs_id')->on('curriculum_subjects');
            $table->foreign('required_cbs_id')->references('cbs_id')->on('curriculum_subjects');
            $table->index('curriculum_subject_cbs_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prerequisites');
    }
};
