<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard as AdminDashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Boquizo\FilamentScrollToTop\ScrollToTopPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;
use App\Filament\Plugins\PhilaAdminTourPlugin;
use Emuniq\FilamentBrowserNotifications\BrowserNotificationsPlugin;

class AdminPanelProvider extends PanelProvider
{
  /**
   * Configure le panneau d'administration Filament PHILA-CE.
   */
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->id('admin')
      ->path('admin')
      ->authGuard('admin')
      ->login(Login::class)
      ->brandName('PHILA-CE')
      ->brandLogo(fn () => view('filament.admin.brand-logo'))
      ->brandLogoHeight('4rem')
      ->darkModeBrandLogo(fn () => view('filament.admin.brand-logo'))
      ->favicon(asset('images/phila-logo.png'))
      ->topbar()
      ->colors([
        'primary' => Color::hex('#F39200'),
      ])
      ->darkMode(true)
      ->databaseNotifications()
      ->databaseNotificationsPolling('30s')
      ->sidebarCollapsibleOnDesktop()
      ->viteTheme('resources/css/filament/admin/theme.css')
      ->navigationGroups([
        'Administration',
        'ECAP',
        'Gestion des cursus',
        'Contenu pédagogique',
        'Apprenants',
        'Évaluations',
        'Progression',
        'Mentorat',
        'Certifications',
        'Prière',
        'Système',
      ])
      ->collapsibleNavigationGroups(true)
      ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
      ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
      ->pages([
        AdminDashboard::class,
      ])
      ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
      ->widgets([])
      ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        PreventRequestForgery::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
      ])
      ->plugins([
        FilamentShieldPlugin::make(),
        FilamentSearchSpotlightPlugin::make()
          ->keyBinding('mod+k')
          ->placeholder('Rechercher une ressource, une page ou une action…')
          ->disableDefaultGlobalSearch(),
        ScrollToTopPlugin::make(),
        PhilaAdminTourPlugin::make(),
        BrowserNotificationsPlugin::make()
          ->dismissCooldownDays(90)
          ->promptDelay(3),
      ])
      ->renderHook(
        PanelsRenderHook::SIMPLE_LAYOUT_START,
        fn (): string => view('filament.admin.simple-toolbar')->render(),
      )
      ->renderHook(
        PanelsRenderHook::BODY_END,
        fn (): string => view('filament.admin.confetti')->render(),
      )
      ->authMiddleware([
        Authenticate::class,
      ]);
  }
}
