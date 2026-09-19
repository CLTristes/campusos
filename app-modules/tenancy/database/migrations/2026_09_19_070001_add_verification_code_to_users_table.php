<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O código de verificação do sign-up livre — hashado como `usr_password`
 * (cast `hashed`), nunca em claro. `usr_email_verified_at` já existia desde o
 * template; faltava o par código+validade para de fato preenchê-la.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('usr_verification_code')->nullable()->after('usr_email_verified_at');
            $table->timestamp('usr_verification_code_expires_at')->nullable()->after('usr_verification_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['usr_verification_code', 'usr_verification_code_expires_at']);
        });
    }
};
