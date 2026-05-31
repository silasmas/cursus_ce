<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Tableau de bord admin PHILA-CE avec description contextuelle.
 */
class Dashboard extends BaseDashboard
{
  /**
   * Sous-titre explicatif sous le titre.
   */
  public function getSubheading(): ?string
  {
    return config('filament_admin_help.dashboard')
      ?? 'Vue d\'ensemble de l\'administration PHILA-CE. Utilisez le menu latéral pour gérer les sessions ECAP, le contenu pédagogique et les fidèles.';
  }
}
