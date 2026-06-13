<?php

namespace App\Support;

/**
 * Détecte le type d'appareil, le navigateur et la plateforme depuis le User-Agent.
 */
class DeviceDetector
{
  /**
   * Analyse le User-Agent HTTP.
   *
   * @param  string|null  $userAgent  En-tête User-Agent
   * @return array{device_type: string, browser: string|null, platform: string|null}
   */
  public static function parse(?string $userAgent): array
  {
    if ($userAgent === null || trim($userAgent) === '') {
      return [
        'device_type' => 'unknown',
        'browser' => null,
        'platform' => null,
      ];
    }

    return [
      'device_type' => self::detectDeviceType($userAgent),
      'browser' => self::detectBrowser($userAgent),
      'platform' => self::detectPlatform($userAgent),
    ];
  }

  /**
   * Détermine mobile, tablette ou desktop.
   *
   * @param  string  $userAgent  En-tête User-Agent
   * @return string mobile|tablet|desktop|unknown
   */
  private static function detectDeviceType(string $userAgent): string
  {
    $ua = strtolower($userAgent);

    if (preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/', $ua)) {
      return 'tablet';
    }

    if (preg_match('/mobile|iphone|ipod|android.*mobile|blackberry|iemobile|opera mini|webos/', $ua)) {
      return 'mobile';
    }

    if (preg_match('/windows|macintosh|mac os x|linux|cros|x11/', $ua)) {
      return 'desktop';
    }

    return 'unknown';
  }

  /**
   * Extrait le nom du navigateur.
   *
   * @param  string  $userAgent  En-tête User-Agent
   * @return string|null Nom du navigateur
   */
  private static function detectBrowser(string $userAgent): ?string
  {
    $patterns = [
      'Edge' => '/Edg\//',
      'Chrome' => '/Chrome\//',
      'Firefox' => '/Firefox\//',
      'Safari' => '/Safari\//',
      'Opera' => '/OPR\//',
    ];

    foreach ($patterns as $name => $pattern) {
      if (preg_match($pattern, $userAgent)) {
        if ($name === 'Safari' && str_contains($userAgent, 'Chrome')) {
          continue;
        }

        return $name;
      }
    }

    return null;
  }

  /**
   * Extrait le système d'exploitation.
   *
   * @param  string  $userAgent  En-tête User-Agent
   * @return string|null Nom de la plateforme
   */
  private static function detectPlatform(string $userAgent): ?string
  {
    $ua = strtolower($userAgent);

    return match (true) {
      str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod') => 'iOS',
      str_contains($ua, 'android') => 'Android',
      str_contains($ua, 'windows') => 'Windows',
      str_contains($ua, 'mac os x') || str_contains($ua, 'macintosh') => 'macOS',
      str_contains($ua, 'linux') => 'Linux',
      default => null,
    };
  }
}
