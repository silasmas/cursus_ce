<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Récupère les vidéos récentes de la chaîne YouTube Phila — Cité d'Exaucement.
 */
class PhilaYouTubeFeedService
{
  private const CHANNEL_ID = 'UCpl-8yOibCjQYPPOU-j3Syg';

  private const RSS_URL = 'https://www.youtube.com/feeds/videos.xml?channel_id='.self::CHANNEL_ID;

  private const CACHE_KEY = 'phila_youtube_recent_video_ids';

  private const CACHE_TTL_SECONDS = 3600;

  /**
   * Identifiants des vidéos récentes (fallback si le flux RSS est indisponible).
   *
   * @var array<int, string>
   */
  private const FALLBACK_VIDEO_IDS = [
    '2OSMir8w5i0',
    'OZtBjHf2rF8',
    'dMEE-lTyz4s',
    'KKX-qGq16Ak',
    'woUhAkiIGDQ',
    'i1pOLCtQ3SM',
    'ZvJHq5o8h6Q',
    'i9Aa_DE989I',
    'c46B-JB644E',
    'MHniuqgBTPI',
    'Ts3ylA0UpFc',
    'kF9dHwokLDU',
    '_oYgFyTTpuo',
    'L-5lSq245RU',
    'aVL7JSU3DBs',
  ];

  /**
   * Retourne les identifiants YouTube des vidéos récentes du canal.
   *
   * @return array<int, string>
   */
  public function recentVideoIds(): array
  {
    return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
      $ids = $this->fetchFromRss();

      return $ids !== [] ? $ids : self::FALLBACK_VIDEO_IDS;
    });
  }

  /**
   * Construit l'URL de visionnage YouTube à partir d'un identifiant vidéo.
   */
  public function watchUrl(string $videoId): string
  {
    return 'https://www.youtube.com/watch?v='.$videoId;
  }

  /**
   * Lit le flux RSS public de la chaîne.
   *
   * @return array<int, string>
   */
  private function fetchFromRss(): array
  {
    try {
      $response = Http::timeout(15)->get(self::RSS_URL);

      if (! $response->successful()) {
        return [];
      }

      $xml = @simplexml_load_string($response->body());

      if ($xml === false) {
        return [];
      }

      $ids = [];

      foreach ($xml->entry as $entry) {
        $ytNs = $entry->children('http://www.youtube.com/xml/schemas/2015');
        $videoId = trim((string) $ytNs->videoId);

        if ($videoId !== '') {
          $ids[] = $videoId;
        }
      }

      return $ids;
    } catch (\Throwable) {
      return [];
    }
  }

  /**
   * Vide le cache des vidéos (utile après mise à jour manuelle).
   */
  public function forgetCache(): void
  {
    Cache::forget(self::CACHE_KEY);
  }
}
