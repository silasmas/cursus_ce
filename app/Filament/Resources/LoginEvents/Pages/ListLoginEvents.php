<?php

namespace App\Filament\Resources\LoginEvents\Pages;

use App\Filament\Resources\LoginEvents\LoginEventResource;
use App\Filament\Resources\LoginEvents\Widgets\LoginDeviceStatsOverview;
use Filament\Resources\Pages\ListRecords;

/**
 * Liste des connexions enregistrées.
 */
class ListLoginEvents extends ListRecords
{
  protected static string $resource = LoginEventResource::class;

  /**
   * Widgets statistiques au-dessus de la liste.
   *
   * @return array<int, class-string>
   */
  protected function getHeaderWidgets(): array
  {
    return [
      LoginDeviceStatsOverview::class,
    ];
  }
}
