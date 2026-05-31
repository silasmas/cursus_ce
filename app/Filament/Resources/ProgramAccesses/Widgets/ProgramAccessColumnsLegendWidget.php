<?php

namespace App\Filament\Resources\ProgramAccesses\Widgets;

use Filament\Widgets\Widget;

/**
 * Légende des colonnes d'action de la liste Accès au cursus.
 */
class ProgramAccessColumnsLegendWidget extends Widget
{
  protected static ?int $sort = 2;

  protected int|string|array $columnSpan = 'full';

  protected static bool $isLazy = false;

  /**
   * Vue Blade affichant la légende des interrupteurs.
   *
   * @var view-string
   */
  protected string $view = 'filament.resources.program-accesses.columns-legend';
}
