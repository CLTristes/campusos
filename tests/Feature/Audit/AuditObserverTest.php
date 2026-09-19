<?php

declare(strict_types=1);

use CampusOs\Core\Models\AuditLog;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Trilha de auditoria — append-only, com o diff real e sem coluna sensível.
 *
 * Testado sobre User: além de ser um model real, é o que carrega a coluna
 * genuinamente sensível do sistema (o hash da senha), então o teste de $hidden
 * verifica um risco de verdade e não um campo inventado para o teste.
 * Substituiu o model de exemplo `Order`, removido no B0.
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
});

it('cria registro em audit_logs ao criar um usuário', function () {
    $user = User::factory()->create();

    $audit = AuditLog::query()
        ->where('aud_table', 'users')
        ->where('aud_record_id', $user->usr_id)
        ->where('aud_action', 'created')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->aud_before)->toBeNull()
        ->and($audit->aud_after)->not->toBeNull()
        ->and($audit->entity_ent_id)->toBe($user->entity_ent_id);
});

it('registra o diff real (antes/depois) em updated', function () {
    $user = User::factory()->create(['usr_name' => 'Nome Antigo']);

    $user->update(['usr_name' => 'Nome Novo']);

    $audit = AuditLog::query()
        ->where('aud_table', 'users')
        ->where('aud_action', 'updated')
        ->latest('aud_created_at')
        ->first();

    expect($audit->aud_before['usr_name'])->toBe('Nome Antigo')
        ->and($audit->aud_after['usr_name'])->toBe('Nome Novo');
});

it('o hash da senha e o código de verificação nunca entram no diff de auditoria', function () {
    $user = User::factory()->create(['usr_password' => 'senha-secreta', 'usr_verification_code' => '482913']);

    $trail = AuditLog::query()
        ->where('aud_table', 'users')
        ->where('aud_record_id', $user->usr_id)
        ->get();

    expect($trail)->not->toBeEmpty();

    foreach ($trail as $audit) {
        expect($audit->aud_after ?? [])->not->toHaveKey('usr_password')
            ->and($audit->aud_after ?? [])->not->toHaveKey('remember_token')
            ->and($audit->aud_after ?? [])->not->toHaveKey('usr_verification_code')
            ->and($audit->aud_before ?? [])->not->toHaveKey('usr_password')
            ->and($audit->aud_before ?? [])->not->toHaveKey('usr_verification_code');
    }
});

it('registra deleted com o estado anterior', function () {
    $user = User::factory()->create();

    $user->delete();

    $audit = AuditLog::query()
        ->where('aud_table', 'users')
        ->where('aud_record_id', $user->usr_id)
        ->where('aud_action', 'deleted')
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->aud_before)->not->toBeNull()
        ->and($audit->aud_after)->toBeNull();
});

it('update sem mudança real não gera audit_log', function () {
    $user = User::factory()->create();
    $before = AuditLog::query()->count();

    $user->update(['usr_name' => $user->usr_name]);

    expect(AuditLog::query()->count())->toBe($before);
});

it('audit_logs registra mutações de todos os tenants (sem EntityScope)', function () {
    User::factory()->create();

    tenantContext(); // troca o tenant do contexto
    User::factory()->create();

    $tenants = AuditLog::query()
        ->where('aud_table', 'users')
        ->pluck('entity_ent_id')
        ->unique();

    expect($tenants)->toHaveCount(2);
});

it('conta a história completa do registro em ordem cronológica', function () {
    $user = User::factory()->create();
    $user->update(['usr_role' => UserRole::Coordinator]);

    $trail = AuditLog::query()
        ->where('aud_table', 'users')
        ->where('aud_record_id', $user->usr_id)
        ->orderBy('aud_created_at')
        ->pluck('aud_action');

    expect($trail->all())->toBe(['created', 'updated']);
});
