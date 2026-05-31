<?php

namespace App\Services\Student;

use App\Enums\AttemptStatus;
use App\Enums\QuestionType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AttemptAnswer;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Gère le passage des tests (QCM + réponses rédigées).
 */
class AssessmentAttemptService
{
  /**
   * Résumé de la tentative la plus récente pour un fidèle.
   *
   * @return array<string, mixed>
   */
  public function summaryForUser(User $user, Assessment $assessment): array
  {
    $attempt = $this->latestAttempt($user, $assessment);
    $remaining = max(0, ($assessment->max_attempts ?? 1) - $this->attemptCount($user, $assessment));
    $readiness = app(AssessmentReadinessService::class)->readinessPayload($assessment);

    return [
      'id' => $assessment->id,
      'title' => $assessment->title,
      'passing_score' => (float) $assessment->passing_score,
      'time_limit_seconds' => $assessment->time_limit_seconds !== null ? (int) $assessment->time_limit_seconds : null,
      'time_limit_label' => $readiness['time_limit_label'],
      'max_attempts' => (int) ($assessment->max_attempts ?? 1),
      'remaining_attempts' => $remaining,
      'passed' => $this->hasPassed($user, $assessment),
      'last_score' => $attempt?->score !== null ? (float) $attempt->score : null,
      'last_status' => $attempt?->status,
      'can_start' => $remaining > 0 && ! $this->hasPassed($user, $assessment) && $readiness['is_ready'],
      'is_ready' => $readiness['is_ready'],
      'required_questions' => $readiness['required_questions'],
      'questions_count' => $readiness['questions_count'],
    ];
  }

  /**
   * Indique si le fidèle a réussi le test.
   */
  public function hasPassed(User $user, Assessment $assessment): bool
  {
    return AssessmentAttempt::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $user->id)
      ->where('passed', true)
      ->where('status', AttemptStatus::Graded->value)
      ->exists();
  }

  /**
   * Indique si le délai du test est dépassé (grace optionnelle en secondes).
   */
  public function isAttemptExpired(AssessmentAttempt $attempt, int $graceSeconds = 0): bool
  {
    $attempt->loadMissing('assessment');

    $limit = (int) ($attempt->assessment?->time_limit_seconds ?? 0);

    if ($limit <= 0 || $attempt->started_at === null) {
      return false;
    }

    return now()->greaterThan(
      $attempt->started_at->copy()->addSeconds($limit + $graceSeconds)
    );
  }

  /**
   * Clôt une tentative expirée avec les réponses vides (score 0).
   */
  public function expireAttempt(AssessmentAttempt $attempt): AssessmentAttempt
  {
    if ($attempt->status !== AttemptStatus::InProgress->value) {
      return $attempt;
    }

    $assessment = $attempt->assessment()->with('questions')->firstOrFail();
    $emptyAnswers = [];

    foreach ($assessment->questions as $question) {
      $emptyAnswers[$question->id] = [];
    }

    return $this->submitAttempt($attempt, $emptyAnswers);
  }

  /**
   * Démarre une nouvelle tentative de test.
   */
  public function startAttempt(User $user, Assessment $assessment): AssessmentAttempt
  {
    if (! app(AssessmentReadinessService::class)->isReady($assessment)) {
      throw new \RuntimeException('Les questions de ce quiz ne sont pas encore disponibles.');
    }

    if ($this->hasPassed($user, $assessment)) {
      throw new \RuntimeException('Ce test est déjà réussi.');
    }

    if ($this->attemptCount($user, $assessment) >= ($assessment->max_attempts ?? 1)) {
      throw new \RuntimeException('Nombre maximum de tentatives atteint.');
    }

    $enrollment = $this->resolveEnrollment($user, $assessment);

    return AssessmentAttempt::query()->create([
      'assessment_id' => $assessment->id,
      'user_id' => $user->id,
      'enrollment_id' => $enrollment?->id,
      'started_at' => now(),
      'status' => AttemptStatus::InProgress->value,
      'passed' => false,
    ]);
  }

  /**
   * Soumet les réponses et calcule le score (QCM auto-corrigés).
   *
   * @param  array<int, array{option_id?: int|null, text?: string|null}>  $answers  Réponses indexées par question_id
   */
  public function submitAttempt(AssessmentAttempt $attempt, array $answers): AssessmentAttempt
  {
    if ($attempt->status !== AttemptStatus::InProgress->value) {
      throw new \RuntimeException('Cette tentative n\'est plus modifiable.');
    }

    if ($this->isAttemptExpired($attempt, graceSeconds: 3)) {
      throw new \RuntimeException('Le temps imparti pour ce test est écoulé.');
    }

    $assessment = $attempt->assessment()->with(['questions.options'])->firstOrFail();

    return DB::transaction(function () use ($attempt, $answers, $assessment) {
      $earnedPoints = 0.0;
      $totalPoints = 0.0;
      $hasWritten = false;

      foreach ($assessment->questions as $question) {
        $totalPoints += (float) $question->points;
        $answerData = $answers[$question->id] ?? [];
        $pointsAwarded = 0.0;

        if ($question->type === QuestionType::Mcq->value) {
          $optionId = $answerData['option_id'] ?? null;
          $selected = $question->options->firstWhere('id', $optionId);

          if ($selected?->is_correct) {
            $pointsAwarded = (float) $question->points;
          }

          AttemptAnswer::query()->updateOrCreate(
            [
              'assessment_attempt_id' => $attempt->id,
              'question_id' => $question->id,
            ],
            [
              'question_option_id' => $optionId,
              'points_awarded' => $pointsAwarded,
            ],
          );
        } else {
          $hasWritten = true;
          $text = trim((string) ($answerData['text'] ?? ''));

          AttemptAnswer::query()->updateOrCreate(
            [
              'assessment_attempt_id' => $attempt->id,
              'question_id' => $question->id,
            ],
            [
              'answer_text' => $text !== '' ? $text : null,
              'points_awarded' => null,
            ],
          );
        }

        $earnedPoints += $pointsAwarded;
      }

      $scorePercent = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
      $allWrittenFilled = $assessment->questions
        ->where('type', QuestionType::Written->value)
        ->every(function (Question $question) use ($answers) {
          $text = trim((string) ($answers[$question->id]['text'] ?? ''));

          return $text !== '';
        });

      $mcqPassed = $scorePercent >= (float) $assessment->passing_score;
      $passed = ! $hasWritten && $mcqPassed && $allWrittenFilled;

      $attempt->update([
        'submitted_at' => now(),
        'score' => $scorePercent,
        'passed' => $passed,
        'status' => $hasWritten
          ? AttemptStatus::Submitted->value
          : AttemptStatus::Graded->value,
      ]);

      $attempt = $attempt->fresh(['answers']);

      if ($hasWritten) {
        app(\App\Services\Ecap\EcapQuizGradingNotifier::class)->notifyGradingRequired($attempt);
      }

      return $attempt;
    });
  }

  /**
   * Résultats détaillés après soumission (score, révisions M5).
   *
   * @return array<string, mixed>
   */
  public function resultPayload(AssessmentAttempt $attempt): array
  {
    $attempt->load([
      'assessment.courseModule',
      'assessment.chapter',
      'answers.question.reviewChapter',
      'answers.question.options',
      'answers.questionOption',
      'gradedBy',
      'gradingComments.author',
    ]);

    $assessment = $attempt->assessment;
    $reviews = [];
    $questions = [];

    foreach ($attempt->answers as $answer) {
      $question = $answer->question;

      if ($question === null) {
        continue;
      }

      if ($question->type === QuestionType::Mcq->value) {
        $selected = $answer->questionOption ?? $question->options->firstWhere('id', $answer->question_option_id);
        $correctOption = $question->options->firstWhere('is_correct', true);
        $isCorrect = $selected?->is_correct === true;

        $entry = [
          'stem' => $question->stem,
          'type' => 'mcq',
          'is_correct' => $isCorrect,
          'selected_label' => $selected?->label,
          'correct_label' => $correctOption?->label,
          'chapter_id' => ($question->reviewChapter ?? $assessment->chapter)?->id,
          'chapter_title' => ($question->reviewChapter ?? $assessment->chapter)?->title,
        ];

        $questions[] = $entry;

        if (! $isCorrect) {
          $reviewChapter = $question->reviewChapter ?? $assessment->chapter;

          if ($reviewChapter) {
            $reviews[] = [
              'question_stem' => $question->stem,
              'selected_label' => $selected?->label,
              'correct_label' => $correctOption?->label,
              'chapter_id' => $reviewChapter->id,
              'chapter_title' => $reviewChapter->title,
            ];
          }
        }
      } else {
        $questions[] = [
          'stem' => $question->stem,
          'type' => 'written',
          'is_correct' => $answer->points_awarded !== null
            ? ((float) $answer->points_awarded >= (float) $question->points)
            : null,
          'answer_text' => $answer->answer_text,
          'answered_at' => $attempt->submitted_at?->format('d/m/Y H:i'),
          'points_awarded' => $answer->points_awarded !== null ? (float) $answer->points_awarded : null,
          'max_points' => (float) $question->points,
          'grader_feedback' => $answer->grader_feedback,
        ];
      }
    }

    $gradingService = app(AssessmentAttemptGradingService::class);
    $isPendingGrading = $gradingService->needsManualGrading($attempt);
    $staffComments = $attempt->gradingComments
      ->map(fn ($comment) => [
        'id' => $comment->id,
        'author_name' => $comment->author?->name ?? User::query()->find($comment->user_id)?->name ?? 'Acteur ECAP',
        'body' => $comment->body,
        'created_at' => $comment->created_at?->format('d/m/Y H:i'),
      ])
      ->values()
      ->all();

    return [
      'attempt_id' => $attempt->id,
      'status' => $attempt->status,
      'is_pending_grading' => $isPendingGrading,
      'passed' => (bool) $attempt->passed,
      'score' => $attempt->score !== null ? (float) $attempt->score : null,
      'passing_score' => (float) $assessment->passing_score,
      'is_module_exit_quiz' => (bool) $assessment->is_module_exit_quiz,
      'assessment' => [
        'id' => $assessment->id,
        'title' => $assessment->title,
      ],
      'module_name' => $assessment->courseModule?->name,
      'submitted_at' => $attempt->submitted_at?->format('d/m/Y H:i'),
      'graded_by_name' => $attempt->gradedBy?->name,
      'graded_at' => ! $isPendingGrading && $attempt->submitted_at !== null
        ? ($attempt->updated_at?->format('d/m/Y H:i'))
        : null,
      'staff_comments' => $staffComments,
      'staffComments' => $staffComments,
      'history_url' => url('/mon-espace/mes-quiz'),
      'historyUrl' => url('/mon-espace/mes-quiz'),
      'feed_url' => route('assessment.result.feed', [$assessment, $attempt], false),
      'feedUrl' => route('assessment.result.feed', [$assessment, $attempt], false),
      'questions' => $questions,
      'reviews' => $reviews,
    ];
  }

  /**
   * Charge une tentative avec questions pour affichage du test.
   *
   * @return array<string, mixed>
   */
  public function attemptPayload(AssessmentAttempt $attempt): array
  {
    $assessment = $attempt->assessment()
      ->with(['questions.options'])
      ->firstOrFail();

    $limit = $assessment->time_limit_seconds !== null ? (int) $assessment->time_limit_seconds : null;
    $expiresAt = null;
    $remainingSeconds = null;

    if ($limit > 0 && $attempt->started_at !== null) {
      $expiresAt = $attempt->started_at->copy()->addSeconds($limit);
      $remainingSeconds = max(0, $expiresAt->getTimestamp() - now()->getTimestamp());
    }

    return [
      'attempt_id' => $attempt->id,
      'assessment' => [
        'id' => $assessment->id,
        'title' => $assessment->title,
        'time_limit_seconds' => $limit,
        'passing_score' => (float) $assessment->passing_score,
      ],
      'expires_at' => $expiresAt?->toIso8601String(),
      'remaining_seconds' => $remainingSeconds,
      'server_now' => now()->toIso8601String(),
      'questions' => $assessment->questions->map(fn (Question $question) => [
        'id' => $question->id,
        'type' => $question->type,
        'stem' => $question->stem,
        'points' => (float) $question->points,
        'options' => $question->type === QuestionType::Mcq->value
          ? $question->options->map(fn ($option) => [
            'id' => $option->id,
            'label' => $option->label,
          ])->values()->all()
          : [],
      ])->values()->all(),
    ];
  }

  /**
   * Nombre de tentatives effectuées.
   */
  private function attemptCount(User $user, Assessment $assessment): int
  {
    return AssessmentAttempt::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $user->id)
      ->whereNotNull('submitted_at')
      ->count();
  }

  /**
   * Dernière tentative soumise.
   */
  private function latestAttempt(User $user, Assessment $assessment): ?AssessmentAttempt
  {
    return AssessmentAttempt::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $user->id)
      ->latest('submitted_at')
      ->first();
  }

  /**
   * Résout l'inscription du fidèle pour le programme du test.
   */
  private function resolveEnrollment(User $user, Assessment $assessment): ?Enrollment
  {
    if (! $assessment->program_id) {
      return null;
    }

    return Enrollment::query()->firstOrCreate(
      [
        'user_id' => $user->id,
        'program_id' => $assessment->program_id,
      ],
      [
        'status' => 'active',
        'enrolled_at' => now(),
      ],
    );
  }
}
