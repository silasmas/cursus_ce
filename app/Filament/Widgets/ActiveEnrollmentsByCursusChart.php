<?php

namespace App\Filament\Widgets;

use App\Services\Analytics\AdminDashboardStatisticsService;
use Filament\Widgets\ChartWidget;

/**
 * Graphique en barres : inscriptions actives par cursus.
 */
class ActiveEnrollmentsByCursusChart extends ChartWidget
{
  protected static ?int $sort = 3;

  protected ?string $heading = 'Inscriptions actives par cursus';

  protected ?string $description = 'Fidèles avec une inscription au statut « actif ».';

  protected int | string | array $columnSpan = [
    'default' => 'full',
    '@xl' => 6,
  ];

  protected string $color = 'primary';

  /**
   * @return array<string, mixed>
   */
  protected function getData(): array
  {
    $chart = app(AdminDashboardStatisticsService::class)->activeEnrollmentsChart();

    return [
      'datasets' => [
        [
          'label' => 'Inscriptions actives',
          'data' => $chart['data'],
          'backgroundColor' => $chart['colors'],
          'borderColor' => $chart['colors'],
          'borderWidth' => 1,
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
    return 'bar';
  }

  /**
   * Options Chart.js pour des barres verticales lisibles.
   *
   * @return array<string, mixed>
   */
  protected function getOptions(): array
  {
    return [
      'plugins' => [
        'legend' => [
          'display' => false,
        ],
      ],
      'scales' => [
        'y' => [
          'beginAtZero' => true,
          'ticks' => [
            'precision' => 0,
          ],
        ],
      ],
    ];
  }
}
