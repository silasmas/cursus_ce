<?php

namespace App\Services\Student;

use App\Models\Assessment;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Support\Facades\Cache;

/**
 * Vérifie qu'un quiz est prêt à être passé (questions suffisantes).
 */
class AssessmentReadinessService
{
  /**
   * @param  ModuleExitQuizService  $moduleExitQuizService  Règles quiz fin de module
   * @param  AdminNotificationService  $adminNotificationService  Alertes admin
   */
  public function __construct(
    private readonly ModuleExitQuizService $moduleExitQuizService,
    private readonly AdminNotificationService $adminNotificationService,
  ) {}

  /**
   * Nombre minimum de questions requis pour ouvrir le quiz.
   */
  public function requiredQuestionCount(Assessment $assessment): int
  {
    if ($assessment->is_module_exit_quiz) {
      return $this->moduleExitQuizService->requiredQuestionsFor($assessment);
    }

    $quota = $assessment->questionQuota();

    return max(1, $quota ?? 1);
  }

  /**
   * Nombre de questions actuellement composées.
   */
  public function composedQuestionCount(Assessment $assessment): int
  {
    if ($assessment->relationLoaded('questions')) {
      return $assessment->questions->count();
    }

    return (int) $assessment->questions()->count();
  }

  /**
   * Indique si le quiz a assez de questions pour être passé.
   */
  public function isReady(Assessment $assessment): bool
  {
    return $this->composedQuestionCount($assessment) >= $this->requiredQuestionCount($assessment);
  }

  /**
   * Libellé lisible de la durée impartie.
   */
  public function timeLimitLabel(Assessment $assessment): string
  {
    $seconds = (int) ($assessment->time_limit_seconds ?? 0);

    if ($seconds <= 0) {
      return 'Illimitée';
    }

    $minutes = (int) ceil($seconds / 60);

    return $minutes === 1 ? '1 minute' : "{$minutes} minutes";
  }

  /**
   * Données de préparation affichées avant le démarrage.
   *
   * @return array<string, mixed>
   */
  public function readinessPayload(Assessment $assessment): array
  {
    $required = $this->requiredQuestionCount($assessment);
    $composed = $this->composedQuestionCount($assessment);

    return [
      'required_questions' => $required,
      'questions_count' => $composed,
      'is_ready' => $composed >= $required,
      'time_limit_label' => $this->timeLimitLabel($assessment),
    ];
  }

  /**
   * Notifie les admins qu'un fidèle a tenté d'accéder à un quiz incomplet (max 1 fois / heure).
   */
  public function notifyAdminsIfEmpty(Assessment $assessment): void
  {
    if ($this->isReady($assessment)) {
      return;
    }

    $cacheKey = "assessment-empty-notified:{$assessment->id}";

    if (Cache::has($cacheKey)) {
      return;
    }

    $assessment->loadMissing(['courseModule', 'chapter']);

    $required = $this->requiredQuestionCount($assessment);
    $composed = $this->composedQuestionCount($assessment);
    $context = collect([
      $assessment->courseModule?->name,
      $assessment->chapter?->title,
    ])->filter()->implode(' · ');

    $title = 'Quiz incomplet';
    $body = "Le quiz « {$assessment->title} » n'a que {$composed}/{$required} question(s) composée(s)."
      .($context !== '' ? " ({$context})" : '')
      .'. Un fidèle a tenté d\'y accéder.';

    $url = url('/admin/assessments/'.$assessment->id.'/edit');

    $this->adminNotificationService->notifyAdmins($title, $body, $url);

    Cache::put($cacheKey, true, now()->addHour());
  }
}
