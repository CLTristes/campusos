<?php

declare(strict_types=1);

use CampusOs\Tenancy\Mail\VerificationCodeMail;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->utfpr = Entity::factory()->create(['ent_email_domain' => 'alunos.utfpr.edu.br']);
});

it('cadastra o aluno com e-mail do domínio institucional e já devolve acesso', function () {
    Mail::fake();

    $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Kurt Pohling',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'felipe@alunos.utfpr.edu.br')
        ->assertJsonPath('data.role', 'student')
        ->assertJsonPath('data.email_verified', false)
        ->assertJsonStructure(['data' => ['id', 'name', 'email'], 'token']);

    $user = User::query()->where('usr_email', 'felipe@alunos.utfpr.edu.br')->firstOrFail();

    expect($user->entity_ent_id)->toBe($this->utfpr->ent_id)
        ->and($user->usr_email_verified_at)->toBeNull();

    Mail::assertSent(VerificationCodeMail::class);
});

it('recusa domínio de e-mail que não pertence a instituição nenhuma', function () {
    $this->postJson('/api/v1/auth/signup', [
        'name' => 'Golpista',
        'email' => 'golpista@gmail.com',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');

    expect(User::query()->where('usr_email', 'golpista@gmail.com')->exists())->toBeFalse();
});

it('recusa e-mail já cadastrado na mesma instituição', function () {
    CampusOs\Core\Tenancy\TenantContext::runAs(
        $this->utfpr->ent_id,
        fn () => User::factory()->create(['usr_email' => 'felipe@alunos.utfpr.edu.br']),
    );

    $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Duplicado',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('a resposta do cadastro nunca carrega o código de verificação nem o hash da senha', function () {
    $body = $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Kurt Pohling',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])->getContent();

    expect($body)->not->toContain('usr_verification_code')
        ->and($body)->not->toContain('usr_password')
        ->and($body)->not->toContain('$2y$');
});

it('confirma o e-mail com o código certo', function () {
    Mail::fake();

    $token = $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Kurt Pohling',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])->json('token');

    $codigo = null;
    Mail::assertSent(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$codigo): bool {
        $codigo = (fn () => $this->code)->call($mail);

        return true;
    });

    $this->withToken($token)
        ->postJson('/api/v1/auth/verify-email', ['code' => $codigo])
        ->assertOk()
        ->assertJsonPath('data.email_verified', true);

    $user = User::query()->where('usr_email', 'felipe@alunos.utfpr.edu.br')->firstOrFail();
    expect($user->usr_email_verified_at)->not->toBeNull()
        ->and($user->usr_verification_code)->toBeNull();
});

it('código errado não verifica nada', function () {
    Mail::fake();

    $token = $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Kurt Pohling',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])->json('token');

    $this->withToken($token)
        ->postJson('/api/v1/auth/verify-email', ['code' => '000000'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

it('código expirado não verifica — precisa reenviar', function () {
    Mail::fake();

    $token = $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Kurt Pohling',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])->json('token');

    $codigo = null;
    Mail::assertSent(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$codigo): bool {
        $codigo = (fn () => $this->code)->call($mail);

        return true;
    });

    User::query()->where('usr_email', 'felipe@alunos.utfpr.edu.br')
        ->update(['usr_verification_code_expires_at' => now()->subMinute()]);

    $this->withToken($token)
        ->postJson('/api/v1/auth/verify-email', ['code' => $codigo])
        ->assertStatus(422)
        ->assertJsonValidationErrors('code');
});

it('reenviar código gera um novo e-mail', function () {
    Mail::fake();

    $token = $this->postJson('/api/v1/auth/signup', [
        'name' => 'Felipe Kurt Pohling',
        'email' => 'felipe@alunos.utfpr.edu.br',
        'password' => 'senha-forte-123',
        'password_confirmation' => 'senha-forte-123',
    ])->json('token');

    $this->withToken($token)->postJson('/api/v1/auth/verify-email/resend')->assertStatus(204);

    Mail::assertSent(VerificationCodeMail::class, 2);
});
