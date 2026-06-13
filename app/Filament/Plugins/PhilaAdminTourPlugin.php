<?php

namespace App\Filament\Plugins;

use App\Filament\Tour\AdminTourStepCollector;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

/**
 * Visite guidée PHILA-CE — menu latéral en français, modale à droite du menu.
 */
class PhilaAdminTourPlugin implements Plugin
{
  protected bool $showTourButton = true;

  protected string $tourButtonIcon = 'heroicon-o-academic-cap';

  protected string $tourButtonColor = 'primary';

  /**
   * Instancie le plugin.
   */
  public static function make(): static
  {
    return app(static::class);
  }

  /**
   * Retourne l'instance enregistrée.
   */
  public static function get(): static
  {
    return filament(app(static::class)->getId());
  }

  /**
   * Identifiant du plugin.
   */
  public function getId(): string
  {
    return 'phila-admin-tour';
  }

  /**
   * Enregistre le bouton et les assets de la visite guidée.
   */
  public function register(Panel $panel): void
  {
    if ($this->showTourButton) {
      $panel->renderHook(
        PanelsRenderHook::USER_MENU_BEFORE,
        fn (): string => Blade::render(
          '<x-filament::icon-button
              icon="'.$this->tourButtonIcon.'"
              color="'.$this->tourButtonColor.'"
              data-shepherd-tour-trigger
              tooltip="'.e(__('admin-tour.tooltip')).'"
          />',
        ),
      );
    }

    $panel->renderHook(
      PanelsRenderHook::BODY_START,
      fn (): string => view('filament.admin.tour', [
        'navigationMap' => AdminTourStepCollector::navigationMap(),
        'tourSteps' => AdminTourStepCollector::collectSteps(),
        'welcomeStep' => AdminTourStepCollector::welcomeStep(),
        'finishStep' => AdminTourStepCollector::finishStep(),
      ])->render(),
    );
  }

  /**
   * Boot du plugin (aucune action).
   */
  public function boot(Panel $panel): void
  {
  }

  /**
   * Affiche ou masque le bouton de visite.
   */
  public function showTourButton(bool $condition = true): static
  {
    $this->showTourButton = $condition;

    return $this;
  }
}
