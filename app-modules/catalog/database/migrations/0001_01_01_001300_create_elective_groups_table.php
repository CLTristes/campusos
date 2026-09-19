<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * elective_groups — o "conjunto de optativas" da matriz.
 *
 * A UTFPR não lista optativas soltas: agrupa num conjunto com código próprio,
 * carga horária a cumprir e uma janela de períodos. Na matriz 45:
 *
 *   [941] Optativas · Período inicial/final: 05/08 · CH 210 · CH semanal 14
 *
 * A CHS do conjunto é dividida pelos períodos em que ele se distribui —
 * 14 / 4 = 3,5 CHS por período, e o histórico confirma esse número não inteiro
 * no quadro de cálculo do período do aluno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elective_groups', function (Blueprint $table): void {
            $table->uuid('elg_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('curriculum_cur_id');
            $table->string('elg_code', 32);
            $table->string('elg_name');
            $table->integer('elg_required_hours')->default(0);
            $table->decimal('elg_weekly_hours', 6, 2)->default(0);
            $table->smallInteger('elg_first_term')->nullable();
            $table->smallInteger('elg_last_term')->nullable();
            $table->timestamp('elg_created_at')->nullable();
            $table->timestamp('elg_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('curriculum_cur_id')->references('cur_id')->on('curricula');
            $table->unique(['curriculum_cur_id', 'elg_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elective_groups');
    }
};
