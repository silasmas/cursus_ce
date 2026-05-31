<?php

namespace App\Services\Student;

use App\Models\Assessment;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Quiz de fin de module ECAP (M5) : 5 questions, 80 %, blocage module suivant.
 */
class ModuleExitQuizService
{
  public const DEFAULT_REQUIRED_QUESTIONS = 5;

  public const PASSING_SCORE = 80;

  /**
   * Nombre de questions configuré pour un quiz (défaut M5 : 5).
   */
  public function requiredQuestionsFor(Assessment $quiz): int
  {
    return max(1, (int) ($quiz->required_questions ?? self::DEFAULT_REQUIRED_QUESTIONS));
  }

  /**
   * @param  AssessmentAttemptService  $attemptService  Tentatives et scores
   */
  public function __construct(
    private readonly AssessmentAttemptService $attemptService,
  ) {}

  /**
   * Quiz de sortie publié pour un module, s'il existe.
   */
  public function quizForModule(int $courseModuleId): ?Assessment
  {
    return Assessment::query()
      ->where('course_module_id', $courseModuleId)
      ->where('is_module_exit_quiz', true)
      ->where('is_published', true)
      ->withCount('questions')
      ->first();
  }

  /**
   * Indique si le fidèle a réussi le quiz ou s'il n'est pas requis.
   */
  public function hasPassedOrNotRequired(User $user, int $courseModuleId): bool
  {
    $quiz = $this->quizForModule($courseModuleId);

    if ($quiz === null) {
      return true;
    }

    return $this->attemptService->hasPassed($user, $quiz);
  }

  /**
   * Indique si le fidèle peut passer le quiz (tous les chapitres du module terminés).
   */
  public function canAttempt(User $user, Assessment $assessment): bool
  {
    if (! $assessment->is_module_exit_quiz || $assessment->course_module_id === null) {
      return false;
    }

    if ($this->attemptService->hasPassed($user, $assessment)) {
      return false;
    }

    return $this->allChaptersCompleted($user, (int) $assessment->course_module_id);
  }

  /**
   * Résumé affiché dans le parcours fidèle pour un module.
   *
   * @param  array<string, mixed>  $moduleSummary  Agrégat du module (completed, total…)
   * @return array<string, mixed>|null
   */
  public function summaryForModule(User $user, int $courseModuleId, array $moduleSummary): ?array
  {
    $quiz = $this->quizForModule($courseModuleId);

    if ($quiz === null) {
      return null;
    }

    $allChaptersDone = ($moduleSummary['total'] ?? 0) > 0
      && ($moduleSummary['completed'] ?? 0) >= ($moduleSummary['total'] ?? 0);

    return [
      'assessment_id' => $quiz->id,
      'title' => $quiz->title,
      'required_questions' => $this->requiredQuestionsFor($quiz),
      'questions_count' => $quiz->questions_count ?? $quiz->questions()->count(),
      'passing_score' => (float) ($quiz->passing_score ?? self::PASSING_SCORE),
      'passed' => $this->attemptService->hasPassed($user, $quiz),
      'can_attempt' => $allChaptersDone && $this->canAttempt($user, $quiz),
      'is_configured' => ($quiz->questions_count ?? 0) >= $this->requiredQuestionsFor($quiz),
    ];
  }

  /**
   * Vérifie que tous les chapitres publiés du module sont terminés.
   */
  public function allChaptersCompleted(User $user, int $courseModuleId): bool
  {
    $chapterIds = Chapter::query()
      ->where('course_module_id', $courseModuleId)
      ->where('is_published', true)
      ->pluck('id');

    if ($chapterIds->isEmpty()) {
      return false;
    }

    $enrollment = $this->resolveEnrollmentForModule($user, $courseModuleId);

    if ($enrollment === null) {
      return false;
    }

    $completedCount = ChapterProgress::query()
      ->where('enrollment_id', $enrollment->id)
      ->whereIn('chapter_id', $chapterIds)
      ->whereNotNull('completed_at')
      ->count();

    return $completedCount >= $chapterIds->count();
  }

  /**
   * Indique si la transition vers le module suivant est autorisée.
   */
  public function moduleTransitionAllows(
    User $user,
    Chapter $previousChapter,
    Chapter $currentChapter,
  ): bool {
    if ($previousChapter->course_module_id === $currentChapter->course_module_id) {
      return true;
    }

    $previousModuleId = $previousChapter->course_module_id;

    if ($previousModuleId === null) {
      return true;
    }

    return $this->hasPassedOrNotRequired($user, (int) $previousModuleId);
  }

  /**
   * Inscription du fidèle pour le programme du module.
   */
  private function resolveEnrollmentForModule(User $user, int $courseModuleId): ?Enrollment
  {
    $module = CourseModule::query()->with('course')->find($courseModuleId);
    $programId = $module?->course?->program_id;

    if ($programId === null) {
      return null;
    }

    return Enrollment::query()
      ->where('user_id', $user->id)
      ->where('program_id', $programId)
      ->latest('enrolled_at')
      ->first();
  }
}
