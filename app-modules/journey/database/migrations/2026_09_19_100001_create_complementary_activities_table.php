<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * complementary_activities — o tracker do aluno para atividades
 * complementares (B7, desafio 5.1): o que o certificado declara
 * (`cac_hours_claimed`) e o que sobrou depois do teto da categoria.
 *
 * `cac_hours_granted` fica reservado para a HOMOLOGAÇÃO manual da
 * coordenação (Fase 3 do desenho, ◇ planejado) — nesta entrega fica sempre
 * nulo. O corte automático pelo teto da categoria é calculado no
 * `ComplementaryHoursReadModel`, agregado POR CATEGORIA, nunca dividido
 * entre linhas (dividir a perda entre certificados é uma pergunta que
 * ninguém faz — todo mundo pergunta "quanto conta no fim").
 *
 * Decisão do dono do produto (19/09/2026, ver
 * docs/dominio/PROGRESSAO.md pendência 3): esta tabela NUNCA soma no
 * `ProgressReadModel`. `ATV001` (a disciplina de 90h da matriz real) é o
 * que de fato conta como aprovado — as duas fontes não se tocam.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complementary_activities', function (Blueprint $table): void {
            $table->uuid('cac_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('registration_reg_id');
            $table->uuid('complementary_category_ccg_id');
            $table->string('cac_title');
            $table->integer('cac_hours_claimed');
            $table->integer('cac_hours_granted')->nullable();
            $table->string('cac_certificate_path')->nullable();
            $table->date('cac_issued_at')->nullable();
            $table->string('cac_status', 16)->default('submitted');
            $table->uuid('reviewer_usr_id')->nullable();
            $table->text('cac_review_notes')->nullable();
            $table->timestamp('cac_created_at')->nullable();
            $table->timestamp('cac_updated_at')->nullable();
            $table->timestamp('cac_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('registration_reg_id')->references('reg_id')->on('registrations');
            $table->foreign('complementary_category_ccg_id')->references('ccg_id')->on('complementary_categories');
            $table->foreign('reviewer_usr_id')->references('usr_id')->on('users');

            // A leitura mais comum: "minhas atividades" e "o total desta categoria".
            $table->index(['registration_reg_id', 'complementary_category_ccg_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complementary_activities');
    }
};
