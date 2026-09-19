<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * registrations — o VÍNCULO do aluno com um curso, sob uma matriz.
 *
 * O agregado central do desafio 5.1. Um aluno pode ter mais de um vínculo
 * (mudou de curso, segunda graduação) e cada um tem a SUA matriz.
 *
 * `curriculum_cur_id` é uma FOTO da matriz vigente no ingresso e NUNCA se
 * atualiza quando o colegiado publica matriz nova — sem esse congelamento, a
 * barra de progresso de toda a instituição mudaria de valor no dia de uma
 * reforma curricular. Mesmo espírito da `units_unt_id` congelada dos
 * enrollments do FibroMais. Ver docs/dominio/PROGRESSAO.md regra 2.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table): void {
            $table->uuid('reg_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('student_std_id');
            $table->uuid('course_crs_id');
            $table->uuid('curriculum_cur_id');
            $table->uuid('entry_term_trm_id');
            $table->string('reg_number', 32);
            $table->string('reg_status', 16)->default('active');
            $table->date('reg_graduated_at')->nullable();
            $table->timestamp('reg_created_at')->nullable();
            $table->timestamp('reg_updated_at')->nullable();
            $table->timestamp('reg_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('student_std_id')->references('std_id')->on('students');
            $table->foreign('course_crs_id')->references('crs_id')->on('courses');
            $table->foreign('curriculum_cur_id')->references('cur_id')->on('curricula');
            $table->foreign('entry_term_trm_id')->references('trm_id')->on('terms');
            $table->unique(['entity_ent_id', 'reg_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
