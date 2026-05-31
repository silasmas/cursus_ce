<?php

namespace App\Console\Commands;

use App\Services\Content\ChapterYouTubeContentService;
use App\Services\Content\PhilaYouTubeFeedService;
use App\Services\Content\YouTubeVideoMirrorService;
use Illuminate\Console\Command;

/**
 * Remplit les chapitres avec texte + vidéos YouTube @phila_cite_dexaucement.
 */
class SeedChapterYouTubeContent extends Command
{
  /**
   * @var string
   */
  protected $signature = 'content:seed-phila-youtube {--refresh-feed : Recharge le flux RSS YouTube} {--mirror : Télécharge les vidéos sur la plateforme après le seed}';

  /**
   * @var string
   */
  protected $description = 'Ajoute un bloc texte et une vidéo YouTube PHILA à chaque chapitre publié';

  /**
   * @param  PhilaYouTubeFeedService  $youtubeFeed  Flux vidéos du canal
   * @param  ChapterYouTubeContentService  $contentService  Alimentation des chapitres
   */
  public function handle(
    PhilaYouTubeFeedService $youtubeFeed,
    ChapterYouTubeContentService $contentService,
    YouTubeVideoMirrorService $mirrorService,
  ): int {
    if ($this->option('refresh-feed')) {
      $youtubeFeed->forgetCache();
    }

    $count = $contentService->seedAllPublishedChapters();

    $this->info("Contenu texte + vidéo appliqué à {$count} chapitre(s) publié(s).");

    if ($this->option('mirror')) {
      $this->info('Mise en miroir des vidéos sur la plateforme…');
      $mirrored = $mirrorService->attachMirrorsToVideoBlocks();
      $this->info("{$mirrored} bloc(s) vidéo hébergé(s) localement.");
    }

    return self::SUCCESS;
  }
}
