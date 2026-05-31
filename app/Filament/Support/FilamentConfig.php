<?php

namespace App\Filament\Support;

/**
 * Accès aux fichiers de config Filament dont les clés contiennent des points.
 */
class FilamentConfig
{
  /**
   * Libellés FR d'une ressource Filament.
   *
   * @return array{singular?: string, plural?: string, navigation?: string, breadcrumb?: string}
   */
  public static function resourceLabels(string $resourceClass): array
  {
    $key = str_replace('\\', '.', $resourceClass);
    $labels = config('filament_labels', []);
    $entry = $labels[$key] ?? null;

    return is_array($entry) ? $entry : [];
  }

  /**
   * Aide admin d'une ressource Filament.
   *
   * @return array<string, mixed>
   */
  public static function resourceAdminHelp(string $resourceClass): array
  {
    $key = str_replace('\\', '.', $resourceClass);
    $resources = config('filament_admin_help.resources', []);
    $entry = $resources[$key] ?? null;

    return is_array($entry) ? $entry : [];
  }

  /**
   * Description de page admin pour une ressource.
   */
  public static function resourceAdminHelpPage(string $resourceClass, string $pageType): ?string
  {
    $help = static::resourceAdminHelp($resourceClass);
    $pages = $help['pages'] ?? [];

    if (! is_array($pages)) {
      return null;
    }

    $configured = $pages[$pageType] ?? $pages['list'] ?? null;

    return filled($configured) ? (string) $configured : null;
  }
}
