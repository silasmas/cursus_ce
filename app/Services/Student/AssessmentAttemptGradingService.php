<?php

namespace App\Services\Student;

use App\Enums\AttemptStatus;
use App\Enums\EcapVacationRole;
use App\Enums\QuestionType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentGradingComment;
use App\Models\AttemptAnswer;
use App\Models\EcapStaffAssignment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Correction des quiz avec réponses rédigées, verrouillage et recalcul du score.
 */
class AssessmentAttemptGradingService
{
  public const LOCK_TTL_MINUTES = 30;

  /**
   * Indique si la tentative attend une correction manuelle.
   */
  public function needsManualGrading(AssessmentAttempt $attempt): bool
  {
    return $attempt->submitted_at !== null
      && $this->hasUngradedWrittenAnswers($attempt);
  }

  /**
   * Indique si des réponses rédigées n'ont pas encore de points.
   */
  public function hasUngradedWrittenAnswers(AssessmentAttempt $attempt): bool
  {
    return AttemptAnswer::query()
      ->where('assessment_attempt_id', $attempt->id)
      ->whereHas('question', fn ($query) => $query->where('type', QuestionType::Written->value))
      ->whereNull('points_awarded')
      ->exists();
  }

  /**
   * Vérifie si l'utilisateur peut corriger cette tentative.
   */
  public function canUserGrade(User $user, AssessmentAttempt $attempt): bool
  {
    if ($this->isAdminGrader($user)) {
      return true;
    }

    $context = $this->resolveGradingContext($attempt);

    if ($context['course_module_id'] === null || $context['academic_session_id'] === null) {
      return false;
    }

    return $this->isStaffGraderForContext($user, $context);
  }

  /**
   * Acquiert le verrou de correction (ou le renouvelle si déjà détenu).
   *
   * @return array{acquired: bool, locked_by?: array{id: int, name: string}|null}
   */
  public function acquireLock(User $user, AssessmentAttempt $attempt): array
  {
    if (! $this->canUserGrade($user, $attempt)) {
      throw ValidationException::withMessages([
        'attempt' => 'Vous n\'êtes pas autorisé à corriger cette tentative.',
      ]);
    }

    $attempt->refresh();

    if (
      $attempt->grading_locked_by_user_id !== null
      && $attempt->grading_locked_by_user_id !== $user->id
      && ! $this->isLockExpired($attempt)
    ) {
      $attempt->loadMissing('gradingLockedBy');

      return [
        'acquired' => false,
        'locked_by' => [
          'id' => $attempt->grading_locked_by_user_id,
          'name' => $attempt->gradingLockedBy?->name ?? 'Un autre correcteur',
        ],
      ];
    }

    $attempt->update([
      'grading_locked_by_user_id' => $user->id,
      'grading_locked_at' => now(),
    ]);

    return ['acquired' => true, 'locked_by' => null];
  }

  /**
   * Libère le verrou si l'utilisateur en est le détenteur.
   */
  public function releaseLock(User $user, AssessmentAttempt $attempt): void
  {
    $attempt->refresh();

    if ($attempt->grading_locked_by_user_id === $user->id) {
      $attempt->update([
        'grading_locked_by_user_id' => null,
        'grading_locked_at' => null,
      ]);
    }
  }

  /**
   * Indique si l'acteur peut modifier les notes (1re correction ou amélioration par le correcteur initial).
   */
  public function canEditAttempt(User $user, AssessmentAttempt $attempt): bool
  {
    if (! $this->canUserGrade($user, $attempt)) {
      return false;
    }

    if ($this->needsManualGrading($attempt)) {
      return true;
    }

    return $attempt->graded_by_user_id !== null
      && (int) $attempt->graded_by_user_id === (int) $user->id;
  }

  /**
   * Indique si l'acteur peut publier un avis après correction.
   */
  public function canCommentOnAttempt(User $user, AssessmentAttempt $attempt): bool
  {
    if (! $this->canUserGrade($user, $attempt)) {
      return false;
    }

    return $attempt->submitted_at !== null && ! $this->needsManualGrading($attempt);
  }

  /**
   * Indique si la tentative contient au moins une réponse rédigée.
   */
  public function hasWrittenAnswers(AssessmentAttempt $attempt): bool
  {
    return AttemptAnswer::query()
      ->where('assessment_attempt_id', $attempt->id)
      ->whereHas('question', fn ($query) => $query->where('type', QuestionType::Written->value))
      ->exists();
  }

  /**
   * Informations de verrou pour l'interface.
   *
   * @return array{is_locked: bool, is_mine: bool, locked_by?: array{id: int, name: string}, can_edit: bool}
   */
  public function lockInfo(User $user, AssessmentAttempt $attempt): array
  {
    $attempt->loadMissing('gradingLockedBy');

    if (! $this->canEditAttempt($user, $attempt)) {
      return [
        'is_locked' => false,
        'is_mine' => false,
        'can_edit' => false,
      ];
    }

    if (! $this->needsManualGrading($attempt)) {
      return [
        'is_locked' => false,
        'is_mine' => true,
        'can_edit' => true,
      ];
    }

    if ($attempt->grading_locked_by_user_id === null || $this->isLockExpired($attempt)) {
      return [
        'is_locked' => false,
        'is_mine' => false,
        'can_edit' => true,
      ];
    }

    $isMine = (int) $attempt->grading_locked_by_user_id === (int) $user->id;

    return [
      'is_locked' => ! $isMine,
      'is_mine' => $isMine,
      'locked_by' => [
        'id' => $attempt->grading_locked_by_user_id,
        'name' => $attempt->gradingLockedBy?->name ?? 'Un autre correcteur',
      ],
      'can_edit' => $isMine,
    ];
  }

  /**
   * Enregistre les notes des réponses rédigées et finalise la tentative.
   *
   * @param  array<int, array{answer_id: int, points_awarded: float|int, grader_feedback?: string|null}>  $grades
   */
  public function gradeWrittenAnswers(User $grader, AssessmentAttempt $attempt, array $grades): AssessmentAttempt
  {
    if (! $this->canUserGrade($grader, $attempt)) {
      throw ValidationException::withMessages([
        'attempt' => 'Vous n\'êtes pas autorisé à corriger cette tentative.',
      ]);
    }

    if (! $this->needsManualGrading($attempt)) {
      throw ValidationException::withMessages([
        'attempt' => 'Cette tentative est déjà corrigée. Utilisez « Améliorer la correction » si vous en êtes l\'auteur.',
      ]);
    }

    $lock = $this->lockInfo($grader, $attempt);

    if (! $lock['can_edit']) {
      $name = $lock['locked_by']['name'] ?? 'un autre correcteur';

      throw ValidationException::withMessages([
        'lock' => "Cette tentative est en cours de correction par {$name}.",
      ]);
    }

    return DB::transaction(function () use ($grader, $attempt, $grades) {
      $this->applyGrades($attempt, $grades);

      return $this->recalculateAndFinalize($attempt->fresh(['assessment.questions', 'answers.question']), $grader);
    });
  }

  /**
   * Met à jour une correction déjà publiée (correcteur initial uniquement).
   *
   * @param  array<int, array{answer_id: int, points_awarded: float|int, grader_feedback?: string|null}>  $grades
   */
  public function updateGradedAnswers(User $grader, AssessmentAttempt $attempt, array $grades): AssessmentAttempt
  {
    if (! $this->canEditAttempt($grader, $attempt) || $this->needsManualGrading($attempt)) {
      throw ValidationException::withMessages([
        'attempt' => 'Vous ne pouvez améliorer que vos propres corrections déjà enregistrées.',
      ]);
    }

    return DB::transaction(function () use ($grader, $attempt, $grades) {
      $this->applyGrades($attempt, $grades);

      return $this->recalculateAndFinalize($attempt->fresh(['assessment.questions', 'answers.question']), $grader);
    });
  }

  /**
   * Ajoute un avis d'un acteur ECAP sur une correction publiée.
   */
  public function addComment(User $staff, AssessmentAttempt $attempt, string $body): AssessmentGradingComment
  {
    if (! $this->canCommentOnAttempt($staff, $attempt)) {
      throw ValidationException::withMessages([
        'body' => 'Vous ne pouvez pas commenter cette correction.',
      ]);
    }

    return AssessmentGradingComment::query()->create([
      'assessment_attempt_id' => $attempt->id,
      'user_id' => $staff->id,
      'body' => $body,
    ])->load('author');
  }

  /**
   * Applique les notes aux réponses rédigées d'une tentative.
   *
   * @param  array<int, array{answer_id: int, points_awarded: float|int, grader_feedback?: string|null}>  $grades
   */
  private function applyGrades(AssessmentAttempt $attempt, array $grades): void
  {
    foreach ($grades as $grade) {
      $answer = AttemptAnswer::query()
        ->with('question')
        ->whereKey($grade['answer_id'])
        ->where('assessment_attempt_id', $attempt->id)
        ->firstOrFail();

      $maxPoints = (float) ($answer->question?->points ?? 0);
      $awarded = min($maxPoints, max(0, (float) $grade['points_awarded']));

      $answer->update([
        'points_awarded' => $awarded,
        'grader_feedback' => filled($grade['grader_feedback'] ?? null) ? $grade['grader_feedback'] : null,
      ]);
    }
  }

  /**
   * Recalcule le score global et marque la tentative comme corrigée.
   */
  public function recalculateAndFinalize(AssessmentAttempt $attempt, User $grader): AssessmentAttempt
  {
    $attempt->loadMissing(['assessment.questions', 'answers']);

    $earnedPoints = 0.0;
    $totalPoints = 0.0;

    foreach ($attempt->assessment->questions as $question) {
      $totalPoints += (float) $question->points;
      $answer = $attempt->answers->firstWhere('question_id', $question->id);
      $earnedPoints += (float) ($answer?->points_awarded ?? 0);
    }

    $scorePercent = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
    $passed = $scorePercent >= (float) $attempt->assessment->passing_score;

    $attempt->update([
      'score' => $scorePercent,
      'passed' => $passed,
      'status' => AttemptStatus::Graded->value,
      'graded_by_user_id' => $grader->id,
      'grading_locked_by_user_id' => null,
      'grading_locked_at' => null,
    ]);

    return $attempt->fresh(['answers.question', 'assessment', 'user', 'gradedBy']);
  }

  /**
   * Staff ECAP à notifier pour une tentative (enseignants, superviseurs, modérateurs).
   *
   * @return Collection<int, User>
   */
  public function staffGradersToNotify(AssessmentAttempt $attempt): Collection
  {
    $context = $this->resolveGradingContext($attempt);

    if ($context['academic_session_id'] === null) {
      return collect();
    }

    $roles = [
      EcapVacationRole::Supervisor->value,
      EcapVacationRole::Teacher->value,
      EcapVacationRole::Moderator->value,
    ];

    $query = EcapStaffAssignment::query()
      ->where('academic_session_id', $context['academic_session_id'])
      ->whereIn('role', $roles)
      ->where('is_active', true)
      ->with('user');

    if ($context['course_module_id'] !== null) {
      $query->where(function ($inner) use ($context) {
        $inner->where(function ($moduleScoped) use ($context) {
          $moduleScoped
            ->whereIn('role', [EcapVacationRole::Supervisor->value, EcapVacationRole::Teacher->value])
            ->where('course_module_id', $context['course_module_id']);
        })->orWhere('role', EcapVacationRole::Moderator->value);
      });
    }

    if ($context['session_vacation_id'] !== null) {
      $vacationId = $context['session_vacation_id'];
      $query->where(function ($inner) use ($vacationId) {
        $inner->whereNull('session_vacation_id')
          ->orWhere('session_vacation_id', $vacationId);
      });
    }

    return $query->get()
      ->pluck('user')
      ->filter()
      ->unique('id')
      ->values();
  }

  /**
   * Payload d'une tentative pour l'écran de correction.
   *
   * @return array<string, mixed>
   */
  public function gradingPayload(AssessmentAttempt $attempt, User $viewer): array
  {
    $attempt->load([
      'user',
      'assessment.chapter',
      'assessment.courseModule',
      'answers.question',
      'gradingLockedBy',
      'gradedBy',
      'gradingComments.author',
    ]);

    $writtenAnswers = $attempt->answers
      ->filter(fn (AttemptAnswer $answer) => $answer->question?->type === QuestionType::Written->value)
      ->values()
      ->map(fn (AttemptAnswer $answer) => [
        'id' => $answer->id,
        'question_id' => $answer->question_id,
        'stem' => $answer->question?->stem,
        'answer_text' => $answer->answer_text,
        'max_points' => (float) ($answer->question?->points ?? 0),
        'points_awarded' => $answer->points_awarded !== null ? (float) $answer->points_awarded : null,
        'grader_feedback' => $answer->grader_feedback,
      ])
      ->all();

    $lock = $this->lockInfo($viewer, $attempt);
    $isPending = $this->needsManualGrading($attempt);

    return [
      'id' => $attempt->id,
      'status' => $attempt->status,
      'is_pending' => $isPending,
      'is_graded' => ! $isPending && $attempt->submitted_at !== null,
      'submitted_at' => $attempt->submitted_at?->format('d/m/Y H:i'),
      'student_name' => $attempt->user?->name,
      'assessment_title' => $attempt->assessment?->title,
      'chapter_title' => $attempt->assessment?->chapter?->title,
      'module_name' => $attempt->assessment?->courseModule?->name,
      'score' => $attempt->score !== null ? (float) $attempt->score : null,
      'passed' => (bool) $attempt->passed,
      'graded_by_user_id' => $attempt->graded_by_user_id,
      'graded_by_name' => $attempt->gradedBy?->name,
      'graded_at' => $attempt->updated_at?->format('d/m/Y H:i'),
      'written_answers' => $writtenAnswers,
      'lock' => $lock,
      'can_edit' => $lock['can_edit'] === true,
      'can_comment' => $this->canCommentOnAttempt($viewer, $attempt),
      'comments' => $attempt->gradingComments->map(fn (AssessmentGradingComment $comment) => [
        'id' => $comment->id,
        'author_name' => $comment->author?->name ?? 'Acteur ECAP',
        'body' => $comment->body,
        'is_mine' => (int) $comment->user_id === (int) $viewer->id,
        'created_at' => $comment->created_at?->diffForHumans(),
        'created_at_full' => $comment->created_at?->format('d/m/Y H:i'),
      ])->values()->all(),
    ];
  }

  /**
   * Résout le contexte session / module / vacation pour une tentative.
   *
   * @return array{course_module_id: ?int, academic_session_id: ?int, session_vacation_id: ?int}
   */
  public function resolveGradingContext(AssessmentAttempt $attempt): array
  {
    $attempt->loadMissing(['assessment.chapter', 'user.profile', 'enrollment']);

    $assessment = $attempt->assessment;
    $courseModuleId = $assessment?->course_module_id ?? $assessment?->chapter?->course_module_id;

    $sessionId = $attempt->user?->profile?->academic_session_id
      ?? $attempt->enrollment?->academic_session_id;

    $vacationId = $attempt->user?->profile?->session_vacation_id
      ?? $attempt->enrollment?->session_vacation_id;

    return [
      'course_module_id' => $courseModuleId !== null ? (int) $courseModuleId : null,
      'academic_session_id' => $sessionId !== null ? (int) $sessionId : null,
      'session_vacation_id' => $vacationId !== null ? (int) $vacationId : null,
    ];
  }

  /**
   * Corrige un statut incohérent (corrigé alors que des réponses rédigées restent sans note).
   */
  public function normalizePendingGradingStatus(AssessmentAttempt $attempt): AssessmentAttempt
  {
    if (
      $this->hasUngradedWrittenAnswers($attempt)
      && $attempt->status === AttemptStatus::Graded->value
    ) {
      $attempt->update([
        'status' => AttemptStatus::Submitted->value,
        'passed' => false,
      ]);

      return $attempt->fresh();
    }

    return $attempt;
  }

  /**
   * Indique si le verrou de correction a expiré.
   */
  public function isLockExpired(AssessmentAttempt $attempt): bool
  {
    if ($attempt->grading_locked_at === null) {
      return true;
    }

    return $attempt->grading_locked_at->copy()->addMinutes(self::LOCK_TTL_MINUTES)->isPast();
  }

  /**
   * Indique si l'utilisateur est admin Filament.
   */
  public function isAdminGrader(User $user): bool
  {
    return $user->hasRole(config('filament-shield.super_admin.name', 'super_admin'), 'admin')
      || $user->hasRole(config('filament-shield.panel_user.name', 'panel_user'), 'admin');
  }

  /**
   * Vérifie si l'utilisateur est acteur ECAP habilité pour ce contexte.
   *
   * @param  array{course_module_id: ?int, academic_session_id: ?int, session_vacation_id: ?int}  $context
   */
  private function isStaffGraderForContext(User $user, array $context): bool
  {
    $sessionId = $context['academic_session_id'];
    $moduleId = $context['course_module_id'];
    $vacationId = $context['session_vacation_id'];

    $assignments = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('academic_session_id', $sessionId)
      ->where('is_active', true)
      ->whereIn('role', [
        EcapVacationRole::Teacher->value,
        EcapVacationRole::Supervisor->value,
        EcapVacationRole::Moderator->value,
      ])
      ->get();

    foreach ($assignments as $assignment) {
      $role = $assignment->role instanceof EcapVacationRole
        ? $assignment->role
        : EcapVacationRole::from($assignment->role);

      if ($role === EcapVacationRole::Moderator) {
        if ($vacationId === null
          || $assignment->session_vacation_id === null
          || $assignment->session_vacation_id === $vacationId) {
          return true;
        }

        continue;
      }

      if (! $this->assignmentMatchesModule($assignment, $moduleId)) {
        continue;
      }

      if ($vacationId === null
        || $assignment->session_vacation_id === null
        || $assignment->session_vacation_id === $vacationId) {
        return true;
      }
    }

    return false;
  }

  /**
   * Indique si l'affectation couvre le module du quiz (null = tous les modules).
   */
  private function assignmentMatchesModule(EcapStaffAssignment $assignment, ?int $moduleId): bool
  {
    if ($assignment->course_module_id === null) {
      return true;
    }

    return $moduleId !== null && (int) $assignment->course_module_id === $moduleId;
  }
}
