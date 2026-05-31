<?php

namespace App\Services\Content;

use App\Models\ContentBlock;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Gère les fichiers vidéo hébergés pour les blocs de contenu.
 */
class ContentBlockMediaService
{
  private const DEMO_RELATIVE_PATH = 'course-videos/demo/sample.mp4';

  private const DEMO_SOURCE_URL = 'https://download.samplelib.com/mp4/sample-5s.mp4';

  /**
   * Lie un fichier uploadé (disque public) au bloc vidéo via MediaAsset.
   *
   * @param  ContentBlock  $block  Bloc de contenu vidéo
   * @param  string  $storedPath  Chemin relatif sur le disque public
   * @return MediaAsset Asset créé ou mis à jour
   */
  public function attachStoredVideo(ContentBlock $block, string $storedPath): MediaAsset
  {
    $asset = MediaAsset::query()->updateOrCreate(
      ['disk' => 'public', 'path' => $storedPath],
      [
        'mime_type' => $this->guessMimeType($storedPath),
        'size_bytes' => Storage::disk('public')->size($storedPath),
        'transcode_status' => 'ready',
      ],
    );

    $block->update(['media_asset_id' => $asset->id]);

    return $asset;
  }

  /**
   * Télécharge une vidéo de démonstration et l'associe aux blocs sans fichier hébergé.
   *
   * @return int Nombre de blocs mis à jour
   */
  public function attachDemoVideoToMissingBlocks(): int
  {
    if (! Storage::disk('public')->exists(self::DEMO_RELATIVE_PATH)) {
      $this->downloadDemoVideo();
    }

    $asset = MediaAsset::query()->firstOrCreate(
      ['disk' => 'public', 'path' => self::DEMO_RELATIVE_PATH],
      [
        'mime_type' => 'video/mp4',
        'size_bytes' => Storage::disk('public')->size(self::DEMO_RELATIVE_PATH),
        'transcode_status' => 'ready',
      ],
    );

    return ContentBlock::query()
      ->where('type', 'video')
      ->whereNull('media_asset_id')
      ->update(['media_asset_id' => $asset->id]);
  }

  /**
   * Télécharge le fichier MP4 de démonstration sur le disque public.
   */
  private function downloadDemoVideo(): void
  {
    Storage::disk('public')->makeDirectory('course-videos/demo');

    $response = Http::timeout(60)->get(self::DEMO_SOURCE_URL);

    if (! $response->successful()) {
      throw new \RuntimeException('Impossible de télécharger la vidéo de démonstration.');
    }

    Storage::disk('public')->put(self::DEMO_RELATIVE_PATH, $response->body());
  }

  /**
   * Devine le type MIME à partir de l'extension du fichier.
   *
   * @param  string  $path  Chemin relatif du fichier
   * @return string Type MIME
   */
  private function guessMimeType(string $path): string
  {
    return str_ends_with(strtolower($path), '.webm') ? 'video/webm' : 'video/mp4';
  }
}
