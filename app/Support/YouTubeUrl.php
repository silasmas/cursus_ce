<?php

namespace App\Support;

/**
 * Normalise les liens YouTube pour le stockage et l'intégration iframe.
 */
class YouTubeUrl
{
  /**
   * Extrait l'identifiant vidéo (11 caractères) depuis une URL YouTube.
   *
   * @param  string|null  $url  Lien watch, youtu.be, embed ou shorts
   * @return string|null Identifiant vidéo ou null si non reconnu
   */
  public static function extractVideoId(?string $url): ?string
  {
    if ($url === null || trim($url) === '') {
      return null;
    }

    $patterns = [
      '#youtube\.com/embed/([a-zA-Z0-9_-]{11})#',
      '#youtube-nocookie\.com/embed/([a-zA-Z0-9_-]{11})#',
      '#youtube\.com/shorts/([a-zA-Z0-9_-]{11})#',
      '#youtu\.be/([a-zA-Z0-9_-]{11})#',
      '#youtube\.com/watch\?(?:[^&]*&)*v=([a-zA-Z0-9_-]{11})#',
    ];

    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
      }
    }

    return null;
  }

  /**
   * Construit l'URL de visionnage standard à enregistrer en base.
   *
   * @param  string  $videoId  Identifiant YouTube
   * @return string URL watch YouTube
   */
  public static function watchUrl(string $videoId): string
  {
    return 'https://www.youtube.com/watch?v='.$videoId;
  }

  /**
   * URL de la miniature YouTube (affichée avant lecture, sans quitter la plateforme).
   *
   * @param  string  $videoId  Identifiant YouTube
   * @return string URL de la vignette hqdefault
   */
  public static function thumbnailUrl(string $videoId): string
  {
    return 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg';
  }

  /**
   * Construit l'URL d'intégration iframe pour le lecteur du portail.
   *
   * @param  string|null  $url  Lien enregistré dans le bloc vidéo
   * @param  string|null  $origin  Origine du site (ex. http://127.0.0.1:8000)
   * @return string|null URL embed ou null si le lien n'est pas YouTube
   */
  public static function embedUrl(?string $url, ?string $origin = null): ?string
  {
    $videoId = self::extractVideoId($url);

    if ($videoId === null) {
      return null;
    }

    $params = [
      'rel' => '0',
      'modestbranding' => '1',
    ];

    if ($origin !== null && $origin !== '') {
      $params['origin'] = $origin;
    }

    return 'https://www.youtube.com/embed/'.$videoId.'?'.http_build_query($params);
  }

  /**
   * Normalise un lien saisi en URL watch (format recommandé en base).
   *
   * @param  string|null  $url  Lien brut saisi dans l'admin
   * @return string|null URL watch ou null si non YouTube
   */
  public static function normalizeWatchUrl(?string $url): ?string
  {
    $videoId = self::extractVideoId($url);

    if ($videoId === null) {
      return $url;
    }

    return self::watchUrl($videoId);
  }
}
