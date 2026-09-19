<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Auth\Login;
use App\Http\Middleware\ResolveTenantFromUser;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Console de dados — ferramenta de DEV/QA, não uma tela do produto.
 *
 * Serve para olhar e corrigir dado real sem abrir o psql: num hackathon é com
 * ele que se conserta um registro errado três minutos antes de apresentar.
 *
 * Duas decisões deliberadas:
 *
 * 1. `ResolveTenantFromUser` entra no authMiddleware, DEPOIS do Authenticate.
 *    Sem ele o painel abriria vazio: o EntityScope filtra por um TenantContext
 *    que ninguém definiu. É o mesmo middleware da API — uma borda só.
 *
 * 2. Usa o `User` de domínio e o guard web padrão, com `canAccessPanel()`
 *    restringindo a coordenação e à gestão (ver User::canAccessPanel). O
 *    FibroMais tem um guard separado (`data_console`) porque o painel dele roda
 *    em produção; aqui isso seria meia hora para proteger uma ferramenta que
 *    não sai do ambiente do hackathon. Se o produto for para produção, o guard
 *    próprio + MFA vira requisito — está anotado como ◇ planejado.
 */
final class DataConsolePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('data-console')
            ->path('data-console')
            ->brandName('CampusOS · Console de dados')
            ->login(Login::class)
            ->colors([
                'primary' => Color::hex('#3d55be'),   // tinta — a marca do CampusOS
                'warning' => Color::hex('#d1911c'),   // marca-texto
                'success' => Color::hex('#2f8253'),   // correção verde
                'danger' => Color::hex('#c9563a'),    // correção vermelha
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                ResolveTenantFromUser::class,
            ]);
    }
}
