<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * note_votes — o voto positivo numa anotação (curadoria contra slop, B5
 * esticada). Sem `SoftDeletes`, exceção documentada como `complementary_
 * categories`: um voto é um fato binário — "votou" ou "não votou" — apagar
 * fisicamente ao desfazer é o comportamento certo, não uma mutação a auditar.
 *
 * O índice único (`note_nte_id`, `user_usr_id`) É a regra de negócio "um voto
 * por pessoa por nota": não existe checagem equivalente em código, o banco é
 * quem garante mesmo sob duas requisições simultâneas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_votes', function (Blueprint $table): void {
            $table->uuid('nvt_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('note_nte_id');
            $table->uuid('user_usr_id');
            $table->timestamp('nvt_created_at')->nullable();
            $table->timestamp('nvt_updated_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('note_nte_id')->references('nte_id')->on('notes');
            $table->foreign('user_usr_id')->references('usr_id')->on('users');

            $table->unique(['note_nte_id', 'user_usr_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_votes');
    }
};
