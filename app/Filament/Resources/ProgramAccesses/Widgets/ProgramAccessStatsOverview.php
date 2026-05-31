<?php

namespace App\Filament\Resources\ProgramAccesses\Widgets;

use App\Models\ProgramAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Statistiques des accès cursus affichées au-dessus de la liste Filament.
 */
class ProgramAccessStatsOverview extends StatsOverviewWidget
{
  /**
   * @return array<int, Stat>
   */
  protected function getStats(): array
  {
    $total = ProgramAccess::query()->count();

    return [
      Stat::make('Total accès', $total)
        ->description('Fidèles × cursus enregistrés')
        ->color('gray'),
      Stat::make('En attente', ProgramAccess::query()->where('is_pending', true)->count())
        ->description('Cursus pas encore ouvert')
        ->color('gray'),
      Stat::make('Ouverts', ProgramAccess::query()->where('is_open', true)->count())
        ->description('Parcours en ligne actif')
        ->color('info'),
      Stat::make('À valider', ProgramAccess::query()->where('needs_admin_validation', true)->count())
        ->description('Déclaration « déjà suivi »')
        ->color('warning'),
      Stat::make('Acquis', ProgramAccess::query()->where('is_completed', true)->count())
        ->description('Cursus validé ou terminé')
        ->color('success'),
      Stat::make('Dispensés', ProgramAccess::query()->where('is_waived', true)->count())
        ->description('Dispense administrative')
        ->color('success'),
    ];
  }
}
