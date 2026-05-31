<?php

namespace App\Filament\Resources\ProgramAccesses\Pages;

use App\Filament\Resources\ProgramAccesses\ProgramAccessResource;
use App\Filament\Resources\ProgramAccesses\Widgets\ProgramAccessColumnsLegendWidget;
use App\Filament\Resources\ProgramAccesses\Widgets\ProgramAccessStatsOverview;
use App\Filament\Resources\Pages\ListRecords;

/**
 * Liste des accès cursus par utilisateur.
 */
class ListProgramAccesses extends ListRecords
{
  protected static string $resource = ProgramAccessResource::class;

  /**
   * Statistiques au-dessus du tableau.
   *
   * @return array<int, class-string>
   */
  protected function getHeaderWidgets(): array
  {
    return [
      ProgramAccessStatsOverview::class,
      ProgramAccessColumnsLegendWidget::class,
    ];
  }

  /**
   * Titre de la page en français.
   */
  public function getTitle(): string
  {
    return 'Accès au cursus';
  }
}

