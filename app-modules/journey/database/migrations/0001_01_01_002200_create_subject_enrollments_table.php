<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * subject_enrollments — a tabela mais importante do sistema.
 *
 * Dela saem AS DUAS coisas: a progressão da graduação (desafio 5.1) e quem tem
 * direito de ver qual acervo (desafio 5.2). É a razão de os dois desafios serem
 * um produto só.
 *
 * O índice único é (vínculo, disciplina, TERMO): cursar de novo em OUTRO
 * semestre é permitido e gera linha nova — é assim que uma reprovação aparece
 * no histórico, e é daí que a contagem de tentativas sai de graça.
 *
 * `offering_ofr_id` é nullable porque o histórico importado traz disciplinas de
 * 2019 cuja turma ninguém cadastrou; sem a turma a progressão funciona igual,
 * só o "meus colegas daquele semestre" não.
 *
 * `sen_attendance` é nullable de propósito: no documento, frequência "*"
 * significa NÃO SE APLICA (exame de suficiência, estágio), não zero. Ver
 * docs/dominio/PROGRESSAO.md regra 6.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_enrollments', function (Blueprint $table): void {
            $table->uuid('sen_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('registration_reg_id');
            $table->uuid('subject_sbj_id');
            $table->uuid('term_trm_id');
            $table->uuid('offering_ofr_id')->nullable();
            $table->string('sen_status', 24);
            $table->string('sen_class_code', 32)->nullable();
            $table->string('sen_class_type', 2)->nullable();
            $table->decimal('sen_grade', 5, 2)->nullable();
            $table->decimal('sen_attendance', 5, 2)->nullable();
            $table->integer('sen_hours_earned')->default(0);
            $table->string('sen_source', 24);
            $table->timestamp('sen_created_at')->nullable();
            $table->timestamp('sen_updated_at')->nullable();
            $table->timestamp('sen_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('registration_reg_id')->references('reg_id')->on('registrations');
            $table->foreign('subject_sbj_id')->references('sbj_id')->on('subjects');
            $table->foreign('term_trm_id')->references('trm_id')->on('terms');
            $table->foreign('offering_ofr_id')->references('ofr_id')->on('offerings');
            $table->unique(['registration_reg_id', 'subject_sbj_id', 'term_trm_id'], 'subject_enrollments_unique');
            $table->index(['registration_reg_id', 'sen_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_enrollments');
    }
};
