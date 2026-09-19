<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Http\Controllers;

use CampusOs\Tenancy\Actions\IssueMcpTokenAction;
use CampusOs\Tenancy\Actions\LoginAction;
use CampusOs\Tenancy\Actions\ResendVerificationCodeAction;
use CampusOs\Tenancy\Actions\SignupAction;
use CampusOs\Tenancy\Actions\VerifyEmailAction;
use CampusOs\Tenancy\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Autenticação
 *
 * Login por e-mail e senha, devolvendo um token Sanctum. O token vai no header
 * `Authorization: Bearer <token>` de toda rota autenticada — e é dele que o
 * sistema descobre a instituição do usuário, nunca de um parâmetro do cliente.
 */
final class AuthController
{
    /**
     * Cadastrar (aluno)
     *
     * Cadastro livre — sem escolher universidade numa lista. A instituição é
     * resolvida pelo **domínio do e-mail**; sem um domínio habilitado para
     * aquele endereço, o cadastro é recusado. Devolve o token **imediatamente**
     * (acesso já liberado) e dispara um e-mail com um código de 6 dígitos para
     * confirmar o endereço — que não bloqueia nada, é só a camada de segurança.
     *
     * @unauthenticated
     *
     * @bodyParam name string required Nome completo. Example: Felipe Kurt Pohling
     * @bodyParam email string required E-mail institucional do aluno. Example: aluno@alunos.utfpr.edu.br
     * @bodyParam password string required Mínimo 8 caracteres. Example: senha-forte-123
     * @bodyParam password_confirmation string required Repita a senha. Example: senha-forte-123
     * @bodyParam device string Rótulo do token. Example: iphone-felipe
     *
     * @response 201 scenario="cadastrado" {"data":{"id":"01a0…","name":"Felipe Kurt Pohling","email":"aluno@alunos.utfpr.edu.br","role":"student","email_verified":false},"token":"1|abc…"}
     * @response 422 scenario="domínio não habilitado" {"message":"Use seu e-mail institucional — este domínio não está habilitado para cadastro.","errors":{"email":["Use seu e-mail institucional — este domínio não está habilitado para cadastro."]}}
     */
    public function signup(Request $request, SignupAction $action): JsonResponse
    {
        /** @var array{user: \CampusOs\Tenancy\Models\User, token: string} $result */
        $result = $action->execute($request->only(['name', 'email', 'password', 'password_confirmation', 'device']));

        return UserResource::make($result['user'])
            ->additional(['token' => $result['token']])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Confirmar e-mail
     *
     * Verifica o código de 6 dígitos enviado no cadastro. Não bloqueia acesso —
     * é uma marca de segurança, não um portão.
     *
     * @authenticated
     *
     * @bodyParam code string required O código de 6 dígitos recebido por e-mail. Example: 482913
     *
     * @response 200 scenario="verificado" {"data":{"id":"01a0…","email_verified":true}}
     * @response 422 scenario="código inválido" {"message":"Código inválido.","errors":{"code":["Código inválido."]}}
     */
    public function verifyEmail(Request $request, VerifyEmailAction $action): UserResource
    {
        $user = $action->execute([
            'user_id' => $request->user()->usr_id,
            'code' => $request->input('code'),
        ]);

        return UserResource::make($user);
    }

    /**
     * Reenviar código de verificação
     *
     * O código anterior pode ter expirado (15 minutos) — gera um novo e reenvia.
     *
     * @authenticated
     *
     * @response 204 scenario="reenviado" {}
     */
    public function resendVerification(Request $request, ResendVerificationCodeAction $action): JsonResponse
    {
        $action->execute(['user_id' => $request->user()->usr_id]);

        return response()->json(status: 204);
    }

    /**
     * Entrar
     *
     * Autentica e devolve o token. O e-mail é único **por instituição**: se o
     * mesmo endereço existir em mais de uma, responde 422 pedindo `entity_id`
     * em vez de escolher uma.
     *
     * @unauthenticated
     *
     * @bodyParam email string required E-mail institucional. Example: aluno@alunos.utfpr.edu.br
     * @bodyParam password string required A senha. Example: campusos
     * @bodyParam entity_id string Só quando o e-mail existe em mais de uma instituição. Example: null
     * @bodyParam device string Rótulo do token, para revogar só este dispositivo depois. Example: iphone-felipe
     *
     * @response 200 scenario="sucesso" {"data":{"id":"01a0…","name":"Felipe Kurt Pohling","email":"aluno@alunos.utfpr.edu.br","role":"student","role_label":"Estudante","registration_number":"2567857","entity_id":"01a0…","enabled_modules":[]},"token":"1|abc…"}
     * @response 422 scenario="credenciais inválidas" {"message":"Credenciais inválidas.","errors":{"email":["Credenciais inválidas."]}}
     */
    public function login(Request $request, LoginAction $action): JsonResponse
    {
        /** @var array{user: \CampusOs\Tenancy\Models\User, token: string} $result */
        $result = $action->execute($request->only(['email', 'password', 'entity_id', 'device']));

        return UserResource::make($result['user']->load('campus'))
            ->additional(['token' => $result['token']])
            ->response();
    }

    /**
     * Quem sou eu
     *
     * O usuário do token atual. É o que a SPA chama no boot para saber que tela abrir.
     *
     * @authenticated
     */
    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user()->load('campus'));
    }

    /**
     * Sair
     *
     * Revoga **apenas o token atual** — os outros dispositivos do usuário seguem logados.
     *
     * @authenticated
     *
     * @response 204 scenario="sucesso" {}
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(status: 204);
    }

    /**
     * Token do copiloto (B8)
     *
     * Um token SEPARADO do login, com o menor escopo possível — `mcp:read`
     * sempre, `mcp:write` só se pedido (habilita a única tool de escrita,
     * `criar_anotacao`). Chamar de novo troca o token anterior: nunca existe
     * mais de um token de copiloto por vez.
     *
     * @authenticated
     *
     * @bodyParam allow_write boolean Habilita a tool de escrita. Default: false. Example: false
     *
     * @response 200 scenario="só leitura" {"token":"2|xyz…","abilities":["mcp:read"]}
     * @response 200 scenario="leitura e escrita" {"token":"2|xyz…","abilities":["mcp:read","mcp:write"]}
     */
    public function mcpToken(Request $request, IssueMcpTokenAction $action): JsonResponse
    {
        $result = $action->execute([
            'user_id' => $request->user()->usr_id,
            'allow_write' => $request->boolean('allow_write'),
        ]);

        return response()->json($result);
    }
}
