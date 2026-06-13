<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Page de connexion Filament personnalisée PHILA-CE.
 */
class Login extends BaseLogin
{
  /**
   * Affiche le logo PHILA-CE au-dessus du formulaire.
   */
  public function hasLogo(): bool
  {
    return true;
  }

  /**
   * Titre affiché sur la page de connexion.
   */
  public function getHeading(): string|Htmlable
  {
    return 'Connexion administration';
  }

  /**
   * Sous-titre de la page.
   */
  public function getSubheading(): string|Htmlable|null
  {
    return 'PHILA-CE — Plateforme de formation';
  }

  /**
   * Largeur du formulaire de connexion.
   */
  public function getMaxWidth(): Width|string|null
  {
    return Width::Large;
  }
}
