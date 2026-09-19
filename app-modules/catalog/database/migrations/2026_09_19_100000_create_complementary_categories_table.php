<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * complementary_categories — o teto por categoria de atividade complementar
 * (B7, desafio 5.1), virado dado. Pertence ao `catalog`, não ao `journey`: é
 * regra da MATRIZ (o que a resolução do colegiado permite), não fato de
 * aluno — mesma razão de `prerequisites` viver aqui.
 *
 * Sem SoftDeletes: dado mestre do catálogo, mesmo padrão de
 * `curriculum_subjects`/`prerequisites` (só tabela transacional usa).
 *
 * Pendência do dono do produto (ver docs/dominio/HORAS_COMPLEMENTARES.md):
 * os tetos reais dependem da resolução do curso — o seed é fictício até lá.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complementary_categories', function (Blueprint $table): void {
            $table->uuid('ccg_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('curriculum_cur_id');
            $table->string('ccg_name');
            $table->integer('ccg_max_hours');
            $table->text('ccg_conversion_note')->nullable();
            $table->timestamp('ccg_created_at')->nullable();
            $table->timestamp('ccg_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('curriculum_cur_id')->references('cur_id')->on('curricula');
            $table->unique(['curriculum_cur_id', 'ccg_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complementary_categories');
    }
};
