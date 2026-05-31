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
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Wezlo\FilamentSearchSpotlight\FilamentSearchSpotlightPlugin;

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
      ->brandLogo(asset('images/phila-logo.png'))
      ->brandLogoHeight('3.5rem')
      ->darkModeBrandLogo(asset('images/phila-logo.png'))
      ->favicon(asset('images/phila-logo.png'))
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
      ->widgets([
        AccountWidget::class,
      ])
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
      ])
      ->authMiddleware([
        Authenticate::class,
      ]);
  }
}
