<?php

declare(strict_types=1);

use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    tenantContext();
    $this->user = User::factory()->create();
});

it('por padrão, o token do copiloto só tem a habilidade de leitura', function () {
    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/auth/mcp-token')
        ->assertOk()
        ->json();

    expect($body['abilities'])->toBe(['mcp:read'])
        ->and($body['token'])->toBeString();
});

it('allow_write acrescenta a habilidade de escrita', function () {
    $body = $this->actingAs($this->user)
        ->postJson('/api/v1/auth/mcp-token', ['allow_write' => true])
        ->assertOk()
        ->json();

    expect($body['abilities'])->toBe(['mcp:read', 'mcp:write']);
});

it('chamar de novo troca o token — o anterior para de funcionar', function () {
    // Sem actingAs() de propósito: precisa de tokens Sanctum DE VERDADE pra
    // provar que o anterior foi revogado — actingAs() força o usuário no
    // guard e sobrevive entre chamadas, mascarando a revogação.
    $loginToken = $this->user->createToken('device')->plainTextToken;

    $primeiro = $this->withToken($loginToken)
        ->postJson('/api/v1/auth/mcp-token')
        ->json('token');

    $segundo = $this->withToken($loginToken)
        ->postJson('/api/v1/auth/mcp-token')
        ->json('token');

    expect($segundo)->not->toBe($primeiro);

    // O guard cacheia o usuário resolvido dentro do MESMO teste (mesmo padrão
    // de AutenticacaoTest::"logout revoga apenas o token usado") — em produção
    // cada request é um container novo e recusa sozinho.
    $this->app['auth']->forgetGuards();

    // O primeiro token de copiloto não existe mais.
    $this->withToken($primeiro)
        ->getJson('/api/v1/auth/me')
        ->assertUnauthorized();

    $this->app['auth']->forgetGuards();

    $this->withToken($segundo)
        ->getJson('/api/v1/auth/me')
        ->assertOk();
});

it('o token do copiloto nunca aparece na resposta de outro usuário', function () {
    $outro = User::factory()->create();

    $this->actingAs($this->user)->postJson('/api/v1/auth/mcp-token')->assertOk();

    // Só documentando o isolamento óbvio: o token pertence a quem pediu.
    expect($this->user->tokens()->where('name', 'mcp')->count())->toBe(1)
        ->and($outro->tokens()->where('name', 'mcp')->count())->toBe(0);
});
