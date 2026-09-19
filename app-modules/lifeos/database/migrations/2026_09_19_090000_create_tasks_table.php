<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * tasks — o organizador pessoal do aluno, e a tarefa da turma no mesmo lugar
 * (B6, desafio 5.2). Ao contrário de `notes`, uma tarefa NÃO nasce sempre
 * privada: "Prova 2 — 14/10" pode nascer direto em `offering`, porque
 * cadastrar já é o ato de compartilhar com a turma (ver `CreateTaskAction`).
 *
 * `origin_tsk_id` é a peça que resolve "adotar sem virar dono da linha do
 * outro": adotar uma tarefa compartilhada CRIA uma cópia apontando pra cá, em
 * vez de todo mundo compartilhar a mesma linha (o primeiro que concluísse,
 * concluiria pra turma inteira). O índice único (owner, origin) impede que um
 * duplo clique em "adotar" gere duas cópias da mesma tarefa pro mesmo aluno.
 *
 * `project_prj_id` não ganha FK: `projects` é ◎ esticada (Fase 4 do LifeOS),
 * ainda sem tabela — a coluna existe hoje pra não exigir migração depois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->uuid('tsk_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('owner_usr_id');
            $table->uuid('subject_sbj_id')->nullable();
            $table->uuid('offering_ofr_id')->nullable();
            $table->uuid('project_prj_id')->nullable();
            $table->uuid('origin_tsk_id')->nullable();
            $table->string('tsk_title');
            $table->text('tsk_description_md')->nullable();
            $table->string('tsk_status', 16)->default('todo');
            $table->string('tsk_kind', 32);
            $table->timestamp('tsk_due_at')->nullable();
            $table->string('tsk_visibility', 32)->default('private');
            $table->timestamp('tsk_created_at')->nullable();
            $table->timestamp('tsk_updated_at')->nullable();
            $table->timestamp('tsk_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('owner_usr_id')->references('usr_id')->on('users');
            $table->foreign('subject_sbj_id')->references('sbj_id')->on('subjects');
            $table->foreign('offering_ofr_id')->references('ofr_id')->on('offerings');
            $table->foreign('origin_tsk_id')->references('tsk_id')->on('tasks');

            // A leitura mais comum: "minha agenda" e "as tarefas desta oferta
            // que ainda não adotei" (índice cobre o whereIn de offering+visibility).
            $table->index(['owner_usr_id', 'tsk_status']);
            $table->index(['offering_ofr_id', 'tsk_visibility']);

            // Guarda de idempotência: duplo clique em "adotar" não gera duas cópias.
            $table->unique(['owner_usr_id', 'origin_tsk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
