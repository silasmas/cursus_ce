<?php

namespace App\Services\Ecap;

use App\Models\Chapter;
use App\Models\User;
use App\Services\Student\ChapterGateService;

/**
 * Contrôle l'accès aux chapitres selon la fenêtre calendaire d'un module ECAP.
 */
class EcapModuleCalendarAccessService
{
  /**
   * @param  EcapModuleCountdownService  $countdownService  Décomptes calendrier modules
   * @param  ChapterGateService  $chapterGateService  Progression chapitre / TP
   */
  public function __construct(
    private readonly EcapModuleCountdownService $countdownService,
    private readonly ChapterGateService $chapterGateService,
  ) {}

  /**
   * Indique si la date de fin calendaire du module est dépassée.
   */
  public function isModuleClosed(User $user, ?int $courseModuleId): bool
  {
    if ($courseModuleId === null) {
      return false;
    }

    $countdown = $this->countdownService->forModule($user, $courseModuleId);

    if ($countdown === null) {
      return false;
    }

    return ($countdown['access_open'] ?? true) === false;
  }

  /**
   * Indique si le fidèle peut rouvrir un chapitre terminé en lecture seule.
   */
  public function allowsChapterReview(User $user, Chapter $chapter): bool
  {
    if (! $this->isModuleClosed($user, $chapter->course_module_id)) {
      return false;
    }

    return $this->chapterGateService->isChapterCompleted($user, $chapter);
  }

  /**
   * Message affiché lorsque le module est fermé.
   */
  public function closedModuleMessage(): string
  {
    return 'La période d\'accès à ce module est terminée. Seuls les chapitres déjà achevés restent consultables en lecture seule.';
  }

}
