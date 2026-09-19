<?php

declare(strict_types=1);

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use SensitiveParameter;

/**
 * Login do console de dados.
 *
 * Existe por um motivo só: a coluna de e-mail do CampusOS é `usr_email`
 * (prefixo de 3 letras em toda coluna, convenção do template), e o
 * `getCredentialsFromFormData` do Filament devolve a chave `email`. Sem esta
 * sobrescrita o `retrieveByCredentials` monta `where email = ?`, a coluna não
 * existe e o login estoura em erro de SQL — não em "senha inválida".
 *
 * A senha não precisa de tradução: o provider usa `getAuthPassword()`, e o
 * model já declara `getAuthPasswordName() === 'usr_password'`.
 */
final class Login extends BaseLogin
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'usr_email' => mb_strtolower(trim((string) $data['email'])),
            'password' => $data['password'],
        ];
    }
}
