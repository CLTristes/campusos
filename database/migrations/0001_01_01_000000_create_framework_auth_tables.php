<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabelas de INFRAESTRUTURA do framework — não seguem as convenções de domínio
 * (sem prefixo de 3 letras, sem SoftDeletes). Não as imite.
 *
 * O `users` do esqueleto do Laravel foi REMOVIDO daqui: no CampusOS o usuário é
 * um model de domínio, com escopo de tenant e auditoria, e vive em
 * `app-modules/tenancy/database/migrations`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            // uuid, não foreignId: a PK de users é UUID (convenção de domínio).
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
