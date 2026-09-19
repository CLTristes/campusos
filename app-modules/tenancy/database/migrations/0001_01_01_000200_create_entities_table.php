<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * entities — o tenant, raiz do multi-tenancy. Toda tabela com escopo de tenant
 * carrega a FK `entity_ent_id` (convenção {tabela_singular}_{pk_origem}) apontando
 * para cá, preenchida e filtrada automaticamente pelo core (Entityable/EntityScope).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table): void {
            $table->uuid('ent_id')->primary();
            $table->string('ent_name');
            $table->string('ent_email')->nullable();
            $table->timestamp('ent_created_at')->nullable();
            $table->timestamp('ent_updated_at')->nullable();
            $table->timestamp('ent_deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
