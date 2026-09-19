<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `ent_email_domain` — o domínio de e-mail que autoriza o cadastro livre do
 * ALUNO (ex.: "alunos.utfpr.edu.br"). É o que resolve a instituição a partir
 * do e-mail no sign-up, sem o aluno escolher a universidade numa lista.
 *
 * Nullable e único: instituição sem domínio configurado simplesmente não
 * aceita cadastro livre (só entra por seeder/coordenação), e dois domínios
 * iguais em duas instituições tornariam a resolução ambígua.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            $table->string('ent_email_domain')->nullable()->unique()->after('ent_email');
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            $table->dropColumn('ent_email_domain');
        });
    }
};
