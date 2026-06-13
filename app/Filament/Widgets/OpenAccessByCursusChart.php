<?php

namespace App\Filament\Widgets;

use App\Services\Analytics\AdminDashboardStatisticsService;
use Filament\Widgets\ChartWidget;

/**
 * Graphique en anneau : répartition des accès cursus ouverts.
 */
class OpenAccessByCursusChart extends ChartWidget
{
  protected static ?int $sort = 4;

  protected ?string $heading = 'Répartition des accès ouverts';

  protected ?string $description = 'Fidèles ayant un parcours débloqué dans Mon Espace.';

  protected int | string | array $columnSpan = [
    'default' => 'full',
    '@xl' => 6,
  ];

  /**
   * @return array<string, mixed>
   */
  protected function getData(): array
  {
    $chart = app(AdminDashboardStatisticsService::class)->openAccessDistributionChart();

    return [
      'datasets' => [
        [
          'label' => 'Accès ouverts',
          'data' => $chart['data'],
          'backgroundColor' => $chart['colors'],
          'borderWidth' => 0,
        ],
      ],
      'labels' => $chart['labels'],
    ];
  }

  /**
   * Type de graphique Chart.js.
   */
  protected function getType(): string
  {
    return 'doughnut';
  }

  /**
   * @return array<string, mixed>
   */
  protected function getOptions(): array
  {
    return [
      'plugins' => [
        'legend' => [
          'position' => 'bottom',
        ],
      ],
    ];
  }
}
