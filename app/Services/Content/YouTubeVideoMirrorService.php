<?php

namespace App\Services\Content;

use App\Models\MediaAsset;
use App\Support\YouTubeUrl;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Télécharge les vidéos YouTube sur le disque public pour lecture native (sans iframe).
 */
class YouTubeVideoMirrorService
{
  private const YT_DLP_WINDOWS_URL = 'https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp.exe';

  private const YT_DLP_LINUX_URL = 'https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp';

  private const VIDEO_DIRECTORY = 'course-videos';

  /**
   * Télécharge ou réutilise une vidéo YouTube et retourne l'asset média associé.
   *
   * @param  string  $videoId  Identifiant YouTube (11 caractères)
   * @return MediaAsset|null Asset créé ou existant, null en cas d'échec
   */
  public function mirrorVideo(string $videoId): ?MediaAsset
  {
    $videoId = trim($videoId);

    if ($videoId === '') {
      return null;
    }

    $existing = $this->findExistingAsset($videoId);

    if ($existing !== null) {
      return $existing;
    }

    if (! $this->downloadVideo($videoId)) {
      return null;
    }

    $path = $this->resolveStoredPath($videoId);

    if ($path === null) {
      return null;
    }

    return $this->createMediaAsset($path);
  }

  /**
   * Associe les fichiers miroir aux blocs vidéo des chapitres publiés.
   *
   * @return int Nombre de blocs mis à jour
   */
  public function attachMirrorsToVideoBlocks(): int
  {
    $blocks = \App\Models\ContentBlock::query()
      ->where('type', 'video')
      ->whereNotNull('url')
      ->get();

    $updated = 0;

    foreach ($blocks as $block) {
      $videoId = YouTubeUrl::extractVideoId($block->url)
        ?? ($block->metadata['youtube_video_id'] ?? null);

      if (! is_string($videoId) || $videoId === '') {
        continue;
      }

      $asset = $this->mirrorVideo($videoId);

      if ($asset === null) {
        continue;
      }

      if ((int) $block->media_asset_id !== (int) $asset->id) {
        $block->update(['media_asset_id' => $asset->id]);
        $updated++;
      }
    }

    return $updated;
  }

  /**
   * Retourne le binaire yt-dlp (téléchargé une seule fois dans storage/app/bin).
   *
   * @return string Chemin absolu vers yt-dlp
   */
  public function ensureBinary(): string
  {
    $binDirectory = storage_path('app/bin');
    $isWindows = PHP_OS_FAMILY === 'Windows';
    $binaryPath = $binDirectory.($isWindows ? '/yt-dlp.exe' : '/yt-dlp');

    if (File::exists($binaryPath)) {
      return $binaryPath;
    }

    File::ensureDirectoryExists($binDirectory);

    $downloadUrl = $isWindows ? self::YT_DLP_WINDOWS_URL : self::YT_DLP_LINUX_URL;
    $response = Http::timeout(120)->get($downloadUrl);

    if (! $response->successful()) {
      throw new \RuntimeException('Impossible de télécharger yt-dlp pour la mise en miroir des vidéos.');
    }

    File::put($binaryPath, $response->body());

    if (! $isWindows) {
      chmod($binaryPath, 0755);
    }

    return $binaryPath;
  }

  /**
   * Télécharge la vidéo YouTube en MP4 progressif (format 18, sans ffmpeg).
   *
   * @param  string  $videoId  Identifiant YouTube
   * @return bool True si le fichier est disponible après téléchargement
   */
  private function downloadVideo(string $videoId): bool
  {
    $directory = Storage::disk('public')->path(self::VIDEO_DIRECTORY);
    File::ensureDirectoryExists($directory);

    $outputTemplate = $directory.DIRECTORY_SEPARATOR.$videoId.'.%(ext)s';
    $binary = $this->ensureBinary();
    $watchUrl = YouTubeUrl::watchUrl($videoId);

    $process = new Process(array_merge(
      [$binary],
      $this->extraDownloadArgs(),
      [
        '-f', '18/best[ext=mp4]/best',
        '--merge-output-format', 'mp4',
        '-o', $outputTemplate,
        '--no-playlist',
        '--no-warnings',
        $watchUrl,
      ],
    ));

    $process->setTimeout(900);
    $process->run();

    return $this->resolveStoredPath($videoId) !== null;
  }

  /**
   * Options supplémentaires yt-dlp (cookies navigateur, runtime JS).
   *
   * @return array<int, string>
   */
  private function extraDownloadArgs(): array
  {
    $args = ['--js-runtimes', 'node'];

    $browser = config('services.youtube_mirror.cookies_browser');

    if (is_string($browser) && $browser !== '') {
      $args[] = '--cookies-from-browser';
      $args[] = $browser;
    }

    return $args;
  }

  /**
   * Cherche le fichier vidéo enregistré pour un identifiant YouTube.
   *
   * @param  string  $videoId  Identifiant YouTube
   * @return string|null Chemin relatif sur le disque public
   */
  private function resolveStoredPath(string $videoId): ?string
  {
    $extensions = ['mp4', 'webm', 'mkv', 'm4v'];

    foreach ($extensions as $extension) {
      $relativePath = self::VIDEO_DIRECTORY.'/'.$videoId.'.'.$extension;

      if (Storage::disk('public')->exists($relativePath)) {
        return $relativePath;
      }
    }

    return null;
  }

  /**
   * Retourne un MediaAsset existant pour une vidéo déjà miroirée.
   *
   * @param  string  $videoId  Identifiant YouTube
   * @return MediaAsset|null Asset trouvé ou null
   */
  private function findExistingAsset(string $videoId): ?MediaAsset
  {
    $path = $this->resolveStoredPath($videoId);

    if ($path === null) {
      return null;
    }

    return MediaAsset::query()->firstOrCreate(
      ['disk' => 'public', 'path' => $path],
      [
        'mime_type' => str_ends_with($path, '.webm') ? 'video/webm' : 'video/mp4',
        'size_bytes' => Storage::disk('public')->size($path),
        'transcode_status' => 'ready',
      ],
    );
  }

  /**
   * Crée l'enregistrement MediaAsset pour un fichier téléchargé.
   *
   * @param  string  $relativePath  Chemin relatif sur le disque public
   * @param  string  $videoId  Identifiant YouTube (pour traçabilité)
   * @return MediaAsset Asset persisté
   */
  private function createMediaAsset(string $relativePath): MediaAsset
  {
    return MediaAsset::query()->updateOrCreate(
      ['disk' => 'public', 'path' => $relativePath],
      [
        'mime_type' => str_ends_with($relativePath, '.webm') ? 'video/webm' : 'video/mp4',
        'size_bytes' => Storage::disk('public')->size($relativePath),
        'transcode_status' => 'ready',
      ],
    );
  }
}
