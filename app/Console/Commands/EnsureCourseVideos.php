<?php

namespace App\Console\Commands;

use App\Services\Content\ContentBlockMediaService;
use Illuminate\Console\Command;

/**
 * Associe une vidéo MP4 hébergée aux blocs sans fichier local.
 */
class EnsureCourseVideos extends Command
{
  /**
   * @var string
   */
  protected $signature = 'content:ensure-course-videos';

  /**
   * @var string
   */
  protected $description = 'Héberge une vidéo MP4 sur la plateforme pour les chapitres sans fichier vidéo local';

  /**
   * @param  ContentBlockMediaService  $mediaService  Gestion des fichiers vidéo
   */
  public function handle(ContentBlockMediaService $mediaService): int
  {
    $this->info('Préparation des vidéos hébergées sur la plateforme…');

    $updated = $mediaService->attachDemoVideoToMissingBlocks();

    $this->info("{$updated} bloc(s) vidéo prêt(s) pour la lecture native.");

    $this->line('Pour vos propres enseignements : uploadez un MP4 dans Admin → Contenus → Média, ou exportez depuis YouTube Studio.');

    return self::SUCCESS;
  }
}
