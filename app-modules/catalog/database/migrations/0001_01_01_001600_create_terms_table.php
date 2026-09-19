<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * terms — o semestre letivo como entidade de primeira classe, não uma string
 * "2026/1" espalhada pelo sistema.
 *
 * É o eixo do tempo do produto inteiro: a matrícula acontece num termo, a
 * anotação do veterano foi escrita num termo, e a previsão de formatura é uma
 * contagem de termos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table): void {
            $table->uuid('trm_id')->primary();
            $table->uuid('entity_ent_id');
            $table->smallInteger('trm_year');
            $table->smallInteger('trm_period');
            $table->date('trm_starts_at')->nullable();
            $table->date('trm_ends_at')->nullable();
            $table->string('trm_status', 16)->default('planned');
            $table->timestamp('trm_created_at')->nullable();
            $table->timestamp('trm_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->unique(['entity_ent_id', 'trm_year', 'trm_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
