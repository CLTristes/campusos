<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [EXEMPLO — REMOVÍVEL] orders — a tabela do módulo de exemplo, seguindo as
 * convenções de docs/arquitetura/BANCO.md: prefixo `ord_`, PK UUID, FK
 * `entity_ent_id`, timestamps prefixados, SoftDeletes e o índice único de
 * idempotência (tenant + ref).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->uuid('ord_id')->primary();
            $table->uuid('entity_ent_id');
            $table->string('ord_ref')->nullable();
            $table->string('ord_customer_name');
            $table->string('ord_customer_email')->nullable();
            $table->decimal('ord_amount', 12, 2);
            $table->string('ord_status');
            $table->string('ord_failure_reason')->nullable();
            $table->text('ord_internal_notes')->nullable();
            $table->timestamp('ord_paid_at')->nullable();
            $table->timestamp('ord_created_at')->nullable();
            $table->timestamp('ord_updated_at')->nullable();
            $table->timestamp('ord_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');

            // Idempotência: a mesma ref não cria dois pedidos DENTRO do tenant
            // (refs nulas não colidem — múltiplos NULL são permitidos).
            $table->unique(['entity_ent_id', 'ord_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
