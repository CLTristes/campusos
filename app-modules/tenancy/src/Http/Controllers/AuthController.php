<?php

declare(strict_types=1);

namespace CampusOs\Tenancy\Http\Controllers;

use CampusOs\Tenancy\Actions\LoginAction;
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
}
