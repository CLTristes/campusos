<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contador desnormalizado: `nte_note_votes` é a fonte da verdade (uma linha por
 * voto, índice único trava voto duplicado), mas ordenar o acervo por score não
 * pode custar um `COUNT` por nota a cada `acervo_da_disciplina`/`buscar_anotacoes`
 * — `ToggleNoteVoteAction` mantém isto em sincronia dentro da mesma transação.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->unsignedInteger('nte_upvotes_count')->default(0)->after('nte_visibility');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table): void {
            $table->dropColumn('nte_upvotes_count');
        });
    }
};
