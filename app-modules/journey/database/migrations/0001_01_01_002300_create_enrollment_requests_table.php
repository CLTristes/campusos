<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * enrollment_requests — o documento que o aluno subiu.
 *
 * O leitor de IA NUNCA escreve matrícula direto: ele preenche `erq_extraction`,
 * o aluno revisa na tela de conferência, e só a confirmação cria
 * `subject_enrollments`. É o que transforma um erro de OCR numa correção de 30
 * segundos em vez de um histórico corrompido que ninguém percebe — e é o que
 * torna aceitável usar IA num dado sensível.
 *
 * `registration_reg_id` é nullable porque o documento pode chegar ANTES do
 * vínculo existir: é do próprio documento que se descobre o curso e o RA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_requests', function (Blueprint $table): void {
            $table->uuid('erq_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('user_usr_id');
            $table->uuid('registration_reg_id')->nullable();
            $table->string('erq_kind', 32);
            $table->string('erq_file_path');
            $table->string('erq_mime', 64);
            $table->string('erq_status', 24);
            $table->json('erq_extraction')->nullable();
            $table->decimal('erq_confidence', 4, 3)->nullable();
            $table->string('erq_provider', 64)->nullable();
            $table->string('erq_failure_reason')->nullable();
            $table->timestamp('erq_parsed_at')->nullable();
            $table->timestamp('erq_confirmed_at')->nullable();
            $table->timestamp('erq_created_at')->nullable();
            $table->timestamp('erq_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('user_usr_id')->references('usr_id')->on('users');
            $table->foreign('registration_reg_id')->references('reg_id')->on('registrations');
            $table->index(['user_usr_id', 'erq_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};
