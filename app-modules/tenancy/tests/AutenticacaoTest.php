<?php

declare(strict_types=1);

use CampusOs\Core\Tenancy\TenantContext;
use CampusOs\Tenancy\Enums\UserRole;
use CampusOs\Tenancy\Models\Campus;
use CampusOs\Tenancy\Models\Entity;
use CampusOs\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->utfpr = tenantContext();
    $this->campus = Campus::factory()->create(['cps_code' => 'FB']);
    $this->aluno = User::factory()->create([
        'usr_email' => 'aluno@alunos.utfpr.edu.br',
        'usr_password' => 'campusos',
        'usr_role' => UserRole::Student,
        'usr_registration_number' => '2567857',
        'campus_cps_id' => $this->campus->cps_id,
    ]);
});

it('autentica e devolve um token', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br',
        'password' => 'campusos',
    ])
        ->assertOk()
        ->assertJsonPath('data.email', 'aluno@alunos.utfpr.edu.br')
        ->assertJsonPath('data.role', 'student')
        ->assertJsonPath('data.registration_number', '2567857')
        ->assertJsonPath('data.campus.code', 'FB')
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role'], 'token']);
});

it('normaliza o e-mail — maiúscula e espaço não impedem o login', function () {
    $this->postJson('/api/v1/auth/login', [
        'email' => '  ALUNO@Alunos.UTFPR.edu.BR ',
        'password' => 'campusos',
    ])->assertOk();
});

it('senha errada e e-mail inexistente dão a MESMA resposta', function () {
    $senhaErrada = $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'errada',
    ])->assertStatus(422);

    $naoExiste = $this->postJson('/api/v1/auth/login', [
        'email' => 'ninguem@alunos.utfpr.edu.br', 'password' => 'campusos',
    ])->assertStatus(422);

    // Respostas diferentes entregariam a lista de quem tem conta.
    expect($senhaErrada->json('errors'))->toBe($naoExiste->json('errors'));
});

it('a resposta do login nunca carrega o hash da senha', function () {
    $body = $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'campusos',
    ])->assertOk()->getContent();

    expect($body)->not->toContain('usr_password')
        ->and($body)->not->toContain('$2y$');
});

it('o mesmo e-mail em duas instituições exige dizer qual', function () {
    $outra = Entity::factory()->create();
    TenantContext::runAs($outra->ent_id, fn () => User::factory()->create([
        'usr_email' => 'aluno@alunos.utfpr.edu.br',
        'usr_password' => 'campusos',
    ]));

    $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'campusos',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('entity_id');

    // Com a instituição informada, o login volta a funcionar.
    $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br',
        'password' => 'campusos',
        'entity_id' => $this->utfpr->ent_id,
    ])
        ->assertOk()
        ->assertJsonPath('data.entity_id', $this->utfpr->ent_id);
});

it('o token identifica o usuário e a instituição dele', function () {
    $token = $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'campusos',
    ])->json('token');

    $this->withToken($token)->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.id', $this->aluno->usr_id)
        ->assertJsonPath('data.entity_id', $this->utfpr->ent_id);
});

it('sem token, a rota autenticada recusa', function () {
    $this->getJson('/api/v1/auth/me')->assertStatus(401);
});

it('logout revoga apenas o token usado', function () {
    $doCelular = $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'campusos', 'device' => 'celular',
    ])->json('token');

    $doNotebook = $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'campusos', 'device' => 'notebook',
    ])->json('token');

    expect(DB::table('personal_access_tokens')->count())->toBe(2);

    $this->withToken($doCelular)->postJson('/api/v1/auth/logout')->assertStatus(204);

    // O guard do Laravel cacheia o usuário resolvido dentro do MESMO teste, então
    // repetir a request com o token morto ainda passaria aqui (em produção, cada
    // request é um container novo e recusa). Esquecer os guards reproduz o
    // comportamento real sem fingir que o teste é outra coisa.
    $this->app['auth']->forgetGuards();

    $this->withToken($doCelular)->getJson('/api/v1/auth/me')->assertStatus(401);
    $this->app['auth']->forgetGuards();
    $this->withToken($doNotebook)->getJson('/api/v1/auth/me')->assertOk();

    // E a prova definitiva, independente de guard: só sobrou um token.
    expect(DB::table('personal_access_tokens')->count())->toBe(1)
        ->and(DB::table('personal_access_tokens')->value('name'))->toBe('notebook');
});

it('o login é limitado por tentativa — força bruta não passa', function () {
    foreach (range(1, 6) as $i) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'errada',
        ]);
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => 'aluno@alunos.utfpr.edu.br', 'password' => 'campusos',
    ])->assertStatus(429);
});
