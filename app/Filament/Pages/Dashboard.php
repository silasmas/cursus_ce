<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActiveEnrollmentsByCursusChart;
use App\Filament\Widgets\AdminGlobalStatsOverview;
use App\Filament\Widgets\CursusProgressChart;
use App\Filament\Widgets\CursusSummaryTableWidget;
use App\Filament\Widgets\MonthlyEnrollmentsTrendChart;
use App\Filament\Widgets\OpenAccessByCursusChart;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Tableau de bord admin PHILA-CE avec statistiques par cursus.
 */
class Dashboard extends BaseDashboard
{
  /**
   * Sous-titre explicatif sous le titre.
   */
  public function getSubheading(): ?string
  {
    return config('filament_admin_help.dashboard')
      ?? 'Vue d\'ensemble des cursus, inscriptions, progression et activité pédagogique.';
  }

  /**
   * Grille 12 colonnes pour disposer tableaux et graphiques côte à côte.
   *
   * @return int | array<string, int | null>
   */
  public function getColumns(): int | array
  {
    return 12;
  }

  /**
   * Widgets affichés sur la page d'accueil admin.
   *
   * @return array<class-string>
   */
  public function getWidgets(): array
  {
    return [
      AdminGlobalStatsOverview::class,
      CursusSummaryTableWidget::class,
      ActiveEnrollmentsByCursusChart::class,
      OpenAccessByCursusChart::class,
      CursusProgressChart::class,
      MonthlyEnrollmentsTrendChart::class,
    ];
  }
}
