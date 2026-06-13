<?php

namespace App\Filament\Widgets;

use App\Services\Analytics\AdminDashboardStatisticsService;
use Filament\Widgets\ChartWidget;

/**
 * Graphique linéaire : tendance des nouvelles inscriptions par cursus.
 */
class MonthlyEnrollmentsTrendChart extends ChartWidget
{
  protected static ?int $sort = 6;

  protected ?string $heading = 'Nouvelles inscriptions (6 derniers mois)';

  protected ?string $description = 'Évolution mensuelle des inscriptions par cursus.';

  protected int | string | array $columnSpan = 'full';

  protected string $color = 'info';

  /**
   * @return array<string, mixed>
   */
  protected function getData(): array
  {
    $trend = app(AdminDashboardStatisticsService::class)->monthlyEnrollmentTrend(6);

    return [
      'datasets' => $trend['datasets'],
      'labels' => $trend['labels'],
    ];
  }

  /**
   * Type de graphique Chart.js.
   */
  protected function getType(): string
  {
    return 'line';
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
