<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * campuses — recorte interno da instituição, NÃO um tenant.
 *
 * Existe porque a UTFPR tem 13 câmpus e o MESMO curso em câmpus diferentes tem
 * matriz diferente (confirmado no histórico real: "Câmpus Francisco Beltrão",
 * curso 25 - Sist. Informação, matriz 45). Oferta e turma também são por câmpus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table): void {
            $table->uuid('cps_id')->primary();
            $table->uuid('entity_ent_id');
            $table->string('cps_name');
            $table->string('cps_code', 16);
            $table->string('cps_city')->nullable();
            $table->timestamp('cps_created_at')->nullable();
            $table->timestamp('cps_updated_at')->nullable();
            $table->timestamp('cps_deleted_at')->nullable();

            $table->foreign('entity_ent_id')->references('ent_id')->on('entities');
            $table->unique(['entity_ent_id', 'cps_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
