<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * users — quem entra no sistema. Model de DOMÍNIO (prefixo, UUID, SoftDeletes,
 * escopo de tenant e auditoria), não o `users` do esqueleto do Laravel.
 *
 * `entity_ent_id` é NOT NULL de propósito no MVP: o Entityable só protege por
 * construção enquanto a coluna não aceitar nulo. O admin de plataforma (único
 * ator sem instituição) fica ◇ planejado — quando entrar, a coluna vira nullable
 * e o EntityScope ganha a exceção explícita, com teste próprio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('usr_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('campus_cps_id')->nullable();
            $table->string('usr_name');
            $table->string('usr_email');
            $table->string('usr_password');
            $table->string('usr_role', 32);
            // RA do portal do aluno (ex.: "2567857" no histórico real).
            $table->string('usr_registration_number', 32)->nullable();
            $table->timestamp('usr_email_verified_at')->nullable();
            // Módulos que o aluno ligou (Finanças nasce desligada — não poluir
            // a imagem do produto). json em vez de tabela: são 3 ou 4 flags.
            $table->json('usr_enabled_modules')->nullable();
            $table->rememberToken();
            $table->timestamp('usr_created_at')->nullable();
            $table->timestamp('usr_updated_at')->nullable();
            $table->timestamp('usr_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('campus_cps_id')->references('cps_id')->on('campuses');
            $table->unique(['entity_ent_id', 'usr_email']);
            // Em Postgres NULLs não colidem em índice único — vários usuários
            // sem RA (coordenador, professor) convivem sem conflito.
            $table->unique(['entity_ent_id', 'usr_registration_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
