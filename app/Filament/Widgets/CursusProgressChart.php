<?php

namespace App\Filament\Widgets;

use App\Services\Analytics\AdminDashboardStatisticsService;
use Filament\Widgets\ChartWidget;

/**
 * Graphique en barres : progression moyenne des chapitres par cursus.
 */
class CursusProgressChart extends ChartWidget
{
  protected static ?int $sort = 5;

  protected ?string $heading = 'Progression moyenne par cursus';

  protected ?string $description = 'Chapitres terminés / (inscrits actifs × chapitres publiés).';

  protected int | string | array $columnSpan = [
    'default' => 'full',
    '@xl' => 6,
  ];

  protected string $color = 'success';

  /**
   * @return array<string, mixed>
   */
  protected function getData(): array
  {
    $chart = app(AdminDashboardStatisticsService::class)->progressByProgramChart();

    return [
      'datasets' => [
        [
          'label' => 'Progression (%)',
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
   * @return array<string, mixed>
   */
  protected function getOptions(): array
  {
    return [
      'indexAxis' => 'y',
      'plugins' => [
        'legend' => [
          'display' => false,
        ],
      ],
      'scales' => [
        'x' => [
          'beginAtZero' => true,
          'max' => 100,
        ],
      ],
    ];
  }
}
