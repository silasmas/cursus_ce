<?php

namespace App\Console\Commands;

use App\Services\Content\YouTubeVideoMirrorService;
use Illuminate\Console\Command;

/**
 * Télécharge les vidéos YouTube PHILA sur le serveur pour lecture native dans le portail.
 */
class MirrorPhilaYouTubeVideos extends Command
{
  /**
   * @var string
   */
  protected $signature = 'content:mirror-phila-videos {--video= : Identifiant YouTube unique à miroirer}';

  /**
   * @var string
   */
  protected $description = 'Héberge localement les vidéos YouTube des blocs de contenu (lecture sans iframe)';

  /**
   * @param  YouTubeVideoMirrorService  $mirrorService  Service de mise en miroir
   */
  public function handle(YouTubeVideoMirrorService $mirrorService): int
  {
    $singleVideoId = $this->option('video');

    if (is_string($singleVideoId) && $singleVideoId !== '') {
      $this->info("Téléchargement de la vidéo {$singleVideoId}…");
      $asset = $mirrorService->mirrorVideo($singleVideoId);

      if ($asset === null) {
        $this->error('Échec du téléchargement. Vérifiez la connexion et réessayez.');

        return self::FAILURE;
      }

      $this->info("Vidéo enregistrée : {$asset->path}");

      return self::SUCCESS;
    }

    $this->info('Téléchargement des vidéos des chapitres (peut prendre plusieurs minutes)…');

    $updated = $mirrorService->attachMirrorsToVideoBlocks();

    $this->info("{$updated} bloc(s) vidéo associé(s) à un fichier hébergé sur la plateforme.");

    return self::SUCCESS;
  }
}
