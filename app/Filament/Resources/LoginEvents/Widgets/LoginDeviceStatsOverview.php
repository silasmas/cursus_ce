<?php

namespace App\Filament\Resources\LoginEvents\Widgets;

use App\Services\Analytics\LoginDeviceStatistics;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Statistiques des connexions par type d'appareil (90 derniers jours, portail fidèle).
 */
class LoginDeviceStatsOverview extends StatsOverviewWidget
{
  /**
   * @return array<int, Stat>
   */
  protected function getStats(): array
  {
    $stats = app(LoginDeviceStatistics::class)->deviceBreakdown(90, 'member');

    if ($stats['total'] === 0) {
      return [
        Stat::make('Connexions enregistrées', '0')
          ->description('Les prochaines connexions alimenteront ces statistiques')
          ->color('gray'),
      ];
    }

    $mobileShare = $stats['percentages']['mobile'] + $stats['percentages']['tablet'];

    return [
      Stat::make('Connexions (90 j.)', (string) $stats['total'])
        ->description('Portail fidèle — toutes plateformes')
        ->color('gray'),
      Stat::make('Mobile', (string) $stats['mobile'])
        ->description($stats['percentages']['mobile'].' % des connexions')
        ->color('success'),
      Stat::make('Tablette', (string) $stats['tablet'])
        ->description($stats['percentages']['tablet'].' % des connexions')
        ->color('warning'),
      Stat::make('Ordinateur', (string) $stats['desktop'])
        ->description($stats['percentages']['desktop'].' % des connexions')
        ->color('info'),
      Stat::make('Part mobile + tablette', number_format($mobileShare, 1).' %')
        ->description('Indicateur pour décider d\'une app native')
        ->color($mobileShare >= 50 ? 'success' : 'gray'),
    ];
  }
}
