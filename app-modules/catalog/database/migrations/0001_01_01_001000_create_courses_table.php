<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * courses — o curso, por câmpus.
 *
 * "Bacharelado em Sistemas de Informação" em Francisco Beltrão é um registro; a
 * homônima em outro câmpus é outro, com matriz própria. `crs_code` é o código
 * que a UTFPR usa no portal ("25"), não um id interno.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table): void {
            $table->uuid('crs_id')->primary();
            $table->uuid('entity_ent_id');
            $table->uuid('campus_cps_id');
            $table->string('crs_code', 32);
            $table->string('crs_name');
            $table->string('crs_degree', 32);
            $table->string('crs_shift', 32)->nullable();
            $table->timestamp('crs_created_at')->nullable();
            $table->timestamp('crs_updated_at')->nullable();
            $table->timestamp('crs_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->foreign('campus_cps_id')->references('cps_id')->on('campuses');
            $table->unique(['entity_ent_id', 'crs_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
