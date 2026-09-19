<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * students — a identidade ACADÊMICA, separada da identidade de LOGIN (users).
 *
 * `user_usr_id` é nullable para a coordenação poder importar uma turma inteira
 * antes de qualquer pessoa criar conta; a conta, quando nasce, se cola ao
 * registro existente pelo documento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->uuid('std_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('user_usr_id')->nullable();
            $table->string('std_name');
            $table->string('std_document', 32)->nullable();
            $table->date('std_born_at')->nullable();
            $table->timestamp('std_created_at')->nullable();
            $table->timestamp('std_updated_at')->nullable();
            $table->timestamp('std_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('user_usr_id')->references('usr_id')->on('users');
            $table->unique('user_usr_id');
            $table->unique(['entity_ent_id', 'std_document']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
