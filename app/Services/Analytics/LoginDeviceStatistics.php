<?php

namespace App\Services\Analytics;

use App\Models\LoginEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Agrège les statistiques de connexion par type d'appareil.
 */
class LoginDeviceStatistics
{
  /**
   * Répartition par type d'appareil sur une période.
   *
   * @param  int  $days  Nombre de jours glissants
   * @param  string|null  $guard  Filtre par garde (member, admin)
   * @return array{total: int, mobile: int, tablet: int, desktop: int, unknown: int, percentages: array<string, float>}
   */
  public function deviceBreakdown(int $days = 90, ?string $guard = 'member'): array
  {
    $since = Carbon::now()->subDays($days);

    $query = LoginEvent::query()->where('logged_in_at', '>=', $since);

    if ($guard !== null) {
      $query->where('guard', $guard);
    }

    $counts = $query
      ->selectRaw('device_type, COUNT(*) as total')
      ->groupBy('device_type')
      ->pluck('total', 'device_type');

    $mobile = (int) ($counts['mobile'] ?? 0);
    $tablet = (int) ($counts['tablet'] ?? 0);
    $desktop = (int) ($counts['desktop'] ?? 0);
    $unknown = (int) ($counts['unknown'] ?? 0);
    $total = $mobile + $tablet + $desktop + $unknown;

    $percent = fn (int $value): float => $total > 0 ? round(($value / $total) * 100, 1) : 0.0;

    return [
      'total' => $total,
      'mobile' => $mobile,
      'tablet' => $tablet,
      'desktop' => $desktop,
      'unknown' => $unknown,
      'percentages' => [
        'mobile' => $percent($mobile),
        'tablet' => $percent($tablet),
        'desktop' => $percent($desktop),
        'unknown' => $percent($unknown),
      ],
    ];
  }

  /**
   * Connexions par jour sur une période (pour graphique).
   *
   * @param  int  $days  Nombre de jours
   * @param  string|null  $guard  Garde ciblée
   * @return Collection<int, object{date: string, total: int}>
   */
  public function dailyTotals(int $days = 30, ?string $guard = 'member'): Collection
  {
    $since = Carbon::now()->subDays($days)->startOfDay();

    $query = LoginEvent::query()
      ->where('logged_in_at', '>=', $since)
      ->selectRaw('DATE(logged_in_at) as date, COUNT(*) as total')
      ->groupBy('date')
      ->orderBy('date');

    if ($guard !== null) {
      $query->where('guard', $guard);
    }

    return $query->get();
  }
}
