<?php

namespace App\Services\Student;

use App\Enums\AssessmentType;
use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssignmentSubmission;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Enrollment;
use App\Models\MentorAssignment;
use App\Models\User;
use App\Services\Program\ProgramSettingService;
use Illuminate\Support\Collection;

/**
 * Vérifie les prérequis (TP, quiz selon configuration cursus) avant de valider une étape.
 */
class ChapterGateService
{
  /**
   * @param  AssessmentAttemptService  $attemptService  Service de tentatives de test
   */
  public function __construct(
    private readonly AssessmentAttemptService $attemptService,
    private readonly ProgramSettingService $programSettingService,
  ) {}

  /**
   * Liste les évaluations publiées d'un chapitre par type.
   *
   * @return Collection<int, Assessment>
   */
  public function assessmentsForChapter(Chapter $chapter, AssessmentType $type): Collection
  {
    return Assessment::query()
      ->where('chapter_id', $chapter->id)
      ->where('type', $type->value)
      ->where('is_published', true)
      ->orderBy('id')
      ->get();
  }

  /**
   * Résumé des prérequis pour l'affichage dans le lecteur de cours.
   *
   * @return array{quizzes: array, tps: array, canComplete: bool, blocking: array<int, string>, isReviewMode: bool}
   */
  public function requirementsSummary(User $user, Chapter $chapter): array
  {
    $isReviewMode = $this->isChapterCompleted($user, $chapter);

    $quizzes = $this->assessmentsForChapter($chapter, AssessmentType::Quiz)
      ->map(fn (Assessment $assessment) => $this->attemptService->summaryForUser($user, $assessment))
      ->values()
      ->all();

    $tps = $this->assessmentsForChapter($chapter, AssessmentType::Tp)
      ->map(fn (Assessment $assessment) => $this->tpSummaryForUser($user, $assessment))
      ->values()
      ->all();

    $blocking = $isReviewMode ? [] : $this->blockingReasons($user, $chapter);

    return [
      'quizzes' => $quizzes,
      'tps' => $tps,
      'has_quiz' => count($quizzes) > 0,
      'has_tp' => count($tps) > 0,
      'canComplete' => $isReviewMode || count($blocking) === 0,
      'blocking' => $blocking,
      'isReviewMode' => $isReviewMode,
    ];
  }

  /**
   * Métadonnées tests/TP pour une étape du parcours (dashboard).
   *
   * @return array<string, mixed>
   */
  public function stepAssessmentMeta(User $user, Chapter $chapter, string $stepStatus): array
  {
    $quizCount = $this->assessmentsForChapter($chapter, AssessmentType::Quiz)->count();
    $tpCount = $this->assessmentsForChapter($chapter, AssessmentType::Tp)->count();
    $isCompleted = $this->isChapterCompleted($user, $chapter);

    $quizPassed = null;
    $tpStatus = null;
    $pendingLabels = [];

    if ($stepStatus !== 'locked') {
      foreach ($this->assessmentsForChapter($chapter, AssessmentType::Quiz) as $quiz) {
        $passed = $this->attemptService->hasPassed($user, $quiz);
        $quizPassed = ($quizPassed ?? true) && $passed;
      }

      if ($quizCount === 0) {
        $quizPassed = null;
      }

      foreach ($this->assessmentsForChapter($chapter, AssessmentType::Tp) as $tp) {
        $submission = $this->latestSubmission($user, $tp);
        $status = $submission?->status ?? 'not_submitted';
        $tpStatus = $status;

        if (! $submission && ! $isCompleted) {
          $pendingLabels[] = 'TP : '.$tp->title;
        } elseif ($submission && $this->requiresMentorApproval($user, $chapter)) {
          if ($submission->mentor_status === 'pending') {
            $pendingLabels[] = 'Aval mentor requis : '.$tp->title;
          }
          if ($submission->mentor_status === 'rejected') {
            $pendingLabels[] = 'TP refusé par le mentor : '.$tp->title;
          }
        }

        if ($submission && $status !== SubmissionStatus::Approved->value && ! $isCompleted) {
          if ($status === SubmissionStatus::Pending->value) {
            $pendingLabels[] = 'Validation formateur : '.$tp->title;
          }
        }
      }

      if ($tpCount === 0) {
        $tpStatus = null;
      }
    }

    return [
      'quiz_count' => $quizCount,
      'tp_count' => $tpCount,
      'has_quiz' => $quizCount > 0,
      'has_tp' => $tpCount > 0,
      'quiz_passed' => $quizPassed,
      'tp_status' => $tpStatus,
      'pending_labels' => $pendingLabels,
    ];
  }

  /**
   * Indique si le fidèle peut marquer le chapitre comme terminé.
   */
  public function canCompleteChapter(User $user, Chapter $chapter): bool
  {
    if ($this->isChapterCompleted($user, $chapter)) {
      return true;
    }

    return count($this->blockingReasons($user, $chapter)) === 0;
  }

  /**
   * Retourne les raisons bloquant la complétion du chapitre.
   *
   * @return array<int, string>
   */
  public function blockingReasons(User $user, Chapter $chapter): array
  {
    $reasons = [];

    $programId = $chapter->course?->program_id;

    if ($programId && $this->programSettingService->requiresQuizPass($programId)) {
      foreach ($this->assessmentsForChapter($chapter, AssessmentType::Quiz) as $quiz) {
        if (! $this->attemptService->hasPassed($user, $quiz)) {
          $reasons[] = 'Réussissez le quiz « '.$quiz->title.' » pour valider cette étape.';
        }
      }
    }

    foreach ($this->assessmentsForChapter($chapter, AssessmentType::Tp) as $tp) {
      $submission = $this->latestSubmission($user, $tp);

      if (! $submission) {
        $reasons[] = 'Remettez le TP « '.$tp->title.' ».';

        continue;
      }

      if ($this->requiresMentorApproval($user, $chapter)) {
        if ($submission->mentor_status === 'pending') {
          $reasons[] = 'Le TP « '.$tp->title.' » attend l\'aval de votre mentor.';

          continue;
        }

        if ($submission->mentor_status === 'rejected') {
          $reasons[] = 'Le TP « '.$tp->title.' » a été refusé par votre mentor. Corrigez-le et remettez-le.';

          continue;
        }
      }

      if ($this->tpUnlockedByMentorOnly($chapter) && $submission->mentor_status === 'approved') {
        continue;
      }

      if ($submission->status === SubmissionStatus::Pending->value) {
        $reasons[] = 'Le TP « '.$tp->title.' » est en attente de validation par le formateur.';
      } elseif ($submission->status === SubmissionStatus::Rejected->value) {
        $reasons[] = 'Le TP « '.$tp->title.' » a été refusé. Merci de le corriger et de le remettre.';
      } elseif ($submission->status !== SubmissionStatus::Approved->value) {
        $reasons[] = 'Le TP « '.$tp->title.' » doit être validé pour continuer.';
      }
    }

    return $reasons;
  }

  /**
   * Résumé d'un TP pour un fidèle.
   *
   * @return array<string, mixed>
   */
  public function tpSummaryForUser(User $user, Assessment $assessment): array
  {
    $submission = $this->latestSubmission($user, $assessment);

    return [
      'id' => $assessment->id,
      'title' => $assessment->title,
      'status' => $submission?->status ?? 'not_submitted',
      'mentor_status' => $submission?->mentor_status ?? null,
      'mentor_notes' => $submission?->mentor_notes,
      'submitted_at' => $submission?->submitted_at?->format('d/m/Y H:i'),
      'grade' => $submission?->grade,
      'grader_notes' => $submission?->grader_notes,
      'can_submit' => ! $submission
        || $submission->status === SubmissionStatus::Rejected->value
        || $submission->mentor_status === 'rejected',
    ];
  }

  /**
   * Dernière remise de TP pour un fidèle.
   */
  private function latestSubmission(User $user, Assessment $assessment): ?AssignmentSubmission
  {
    return AssignmentSubmission::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $user->id)
      ->where('visible_to_mentee', true)
      ->where('admin_publication_status', 'published')
      ->latest('submitted_at')
      ->first();
  }

  /**
   * Indique si le fidèle a déjà terminé cette étape.
   */
  public function isChapterCompleted(User $user, Chapter $chapter): bool
  {
    $programId = $chapter->course?->program_id;

    if (! $programId) {
      return false;
    }

    $enrollment = Enrollment::query()
      ->where('user_id', $user->id)
      ->where('program_id', $programId)
      ->first();

    if (! $enrollment) {
      return false;
    }

    return ChapterProgress::query()
      ->where('enrollment_id', $enrollment->id)
      ->where('chapter_id', $chapter->id)
      ->whereNotNull('completed_at')
      ->exists();
  }

  /**
   * Indique si le programme exige l'aval du mentor pour les TP.
   */
  private function requiresMentorApproval(User $user, Chapter $chapter): bool
  {
    $slug = $chapter->course?->program?->slug;
    $programs = config('cursus.mentor_approval_programs', ['metamorpho', 'ecap']);

    if (! $slug || ! in_array($slug, $programs, true)) {
      return false;
    }

    return MentorAssignment::query()
      ->where('mentee_id', $user->id)
      ->where('status', 'active')
      ->exists();
  }

  /**
   * Indique si l'aval mentor seul suffit (sans validation formateur).
   */
  private function tpUnlockedByMentorOnly(Chapter $chapter): bool
  {
    $slug = $chapter->course?->program?->slug;

    return $slug && in_array($slug, config('cursus.mentor_only_tp_programs', ['metamorpho']), true);
  }
}
