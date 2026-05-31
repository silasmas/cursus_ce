<?php

namespace App\Services\Content;

use App\Models\Chapter;
use App\Models\ContentBlock;

/**
 * Alimente chaque chapitre publié avec un bloc texte et une vidéo YouTube PHILA.
 */
class ChapterYouTubeContentService
{
  /**
   * @param  PhilaYouTubeFeedService  $youtubeFeed  Vidéos du canal @phila_cite_dexaucement
   */
  public function __construct(
    private readonly PhilaYouTubeFeedService $youtubeFeed,
  ) {}

  /**
   * Crée ou met à jour texte + vidéo pour tous les chapitres publiés.
   *
   * @return int Nombre de chapitres traités
   */
  public function seedAllPublishedChapters(): int
  {
    $videoIds = $this->youtubeFeed->recentVideoIds();

    if ($videoIds === []) {
      return 0;
    }

    $chapters = Chapter::query()
      ->where('is_published', true)
      ->with(['course.program', 'courseModule'])
      ->orderBy('course_id')
      ->orderBy('course_module_id')
      ->orderBy('sort_order')
      ->orderBy('id')
      ->get();

    foreach ($chapters as $index => $chapter) {
      $videoId = $videoIds[$index % count($videoIds)];
      $this->seedChapter($chapter, $videoId);
    }

    return $chapters->count();
  }

  /**
   * Alimente un chapitre avec un bloc texte et un bloc vidéo.
   */
  public function seedChapter(Chapter $chapter, string $videoId): void
  {
    ContentBlock::query()->updateOrCreate(
      [
        'chapter_id' => $chapter->id,
        'type' => 'text',
        'sort_order' => 1,
      ],
      [
        'title' => 'Enseignement — '.$chapter->title,
        'body' => $this->buildTextBody($chapter),
      ],
    );

    ContentBlock::query()->updateOrCreate(
      [
        'chapter_id' => $chapter->id,
        'type' => 'video',
        'sort_order' => 2,
      ],
      [
        'title' => 'Vidéo — Phila Cité d\'Exaucement',
        'body' => 'Enseignement vidéo de la chaîne officielle Phila — Cité d\'Exaucement (@phila_cite_dexaucement).',
        'url' => $this->youtubeFeed->watchUrl($videoId),
        'metadata' => [
          'youtube_video_id' => $videoId,
          'channel' => '@phila_cite_dexaucement',
        ],
      ],
    );
  }

  /**
   * Rédige un texte d'introduction pour l'étape pédagogique.
   */
  private function buildTextBody(Chapter $chapter): string
  {
    $programName = $chapter->course?->program?->name ?? 'PHILA-CE';
    $moduleName = $chapter->courseModule?->name;
    $courseName = $chapter->course?->name;

    $lines = [
      "Bienvenue à l'étape « {$chapter->title} » du cursus {$programName}.",
    ];

    if ($courseName) {
      $lines[] = "Parcours : {$courseName}.";
    }

    if ($moduleName) {
      $lines[] = "Module : {$moduleName}.";
    }

    $lines[] = '';
    $lines[] = 'Prenez le temps de lire ce contenu, puis visionnez la vidéo d\'enseignement ci-dessous (chaîne YouTube Phila — Cité d\'Exaucement).';
    $lines[] = 'Lorsque vous avez assimilé l\'étape, cliquez sur « Terminer l\'étape » pour débloquer la suivante.';
    $lines[] = '';
    $lines[] = '« Exposez-vous à la Parole de Dieu pour que la vie de Christ se manifeste en vous. » — Vision PHILA';

    return implode("\n", $lines);
  }
}
