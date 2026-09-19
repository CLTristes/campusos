<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Documentação da API
|--------------------------------------------------------------------------
|
| A UI é o Scalar lendo o openapi.yaml que o `php artisan scribe:generate`
| escreve a partir das rotas e Controllers reais — por isso ela nunca
| "desatualiza sozinha": ou reflete o código, ou o generate não rodou.
|
| Scribe com 'add_routes' => false justamente para estas três rotas existirem
| aqui, explícitas, em vez de mágicas dentro do pacote.
|
*/
Route::get('/docs/api', fn () => view('docs.api'))->name('docs.api');

Route::get('/docs/api/openapi.yaml', fn () => response()->file(
    storage_path('app/private/scribe/openapi.yaml'),
    ['Content-Type' => 'application/yaml; charset=utf-8'],
))->name('docs.api.spec');

Route::get('/docs/api/postman.json', fn () => response()->file(
    storage_path('app/private/scribe/collection.json'),
    ['Content-Type' => 'application/json; charset=utf-8'],
))->name('docs.api.postman');
