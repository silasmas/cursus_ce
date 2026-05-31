<?php

namespace App\Services\Student;

use App\Enums\AttemptStatus;
use App\Enums\QuestionType;
use App\Models\AssessmentAttempt;
use App\Models\User;

/**
 * Historique des quiz passés par un fidèle.
 */
class StudentQuizHistoryService
{
  /**
   * @param  AssessmentAttemptGradingService  $gradingService  État des corrections
   */
  public function __construct(
    private readonly AssessmentAttemptGradingService $gradingService,
  ) {}

  /**
   * Tentatives soumises du fidèle, les plus récentes en premier.
   *
   * @return array<int, array<string, mixed>>
   */
  public function attemptsForUser(User $user): array
  {
    return AssessmentAttempt::query()
      ->where('user_id', $user->id)
      ->whereNotNull('submitted_at')
      ->with(['assessment.courseModule', 'assessment.chapter'])
      ->latest('submitted_at')
      ->get()
      ->map(fn (AssessmentAttempt $attempt) => $this->mapAttempt($attempt))
      ->values()
      ->all();
  }

  /**
   * Nombre de quiz en attente de correction manuelle.
   */
  public function pendingGradingCountForUser(User $user): int
  {
    return AssessmentAttempt::query()
      ->where('user_id', $user->id)
      ->where('status', AttemptStatus::Submitted->value)
      ->whereNotNull('submitted_at')
      ->get()
      ->filter(fn (AssessmentAttempt $attempt) => $this->gradingService->needsManualGrading($attempt))
      ->count();
  }

  /**
   * Formate une tentative pour l'interface fidèle.
   *
   * @return array<string, mixed>
   */
  private function mapAttempt(AssessmentAttempt $attempt): array
  {
    $assessment = $attempt->assessment;
    $isPending = $this->gradingService->needsManualGrading($attempt);
    $statusKey = $isPending ? 'pending_grading' : ($attempt->status ?? 'graded');
    $statusLabel = match (true) {
      $isPending => 'En attente de correction',
      $attempt->passed => 'Réussi',
      default => 'Terminé',
    };

    return [
      'id' => $attempt->id,
      'assessment_id' => $assessment?->id,
      'title' => $assessment?->title ?? 'Quiz',
      'module_name' => $assessment?->courseModule?->name,
      'chapter_title' => $assessment?->chapter?->title,
      'is_module_exit_quiz' => (bool) ($assessment?->is_module_exit_quiz ?? false),
      'submitted_at' => $attempt->submitted_at?->format('d/m/Y H:i'),
      'score' => $attempt->score !== null ? (float) $attempt->score : null,
      'passed' => (bool) $attempt->passed,
      'status_key' => $statusKey,
      'status_label' => $statusLabel,
      'is_pending_grading' => $isPending,
      'result_url' => $assessment
        ? url('/mon-espace/tests/'.$assessment->id.'/resultat/'.$attempt->id)
        : null,
    ];
  }
}
