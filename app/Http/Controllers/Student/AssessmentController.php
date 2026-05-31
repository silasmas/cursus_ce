<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Chapter;
use App\Services\Student\AssessmentAttemptService;
use App\Services\Student\AssessmentReadinessService;
use App\Services\Student\ChapterProgressService;
use App\Services\Student\ModuleExitQuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Passage des tests (QCM + réponses rédigées) par les fidèles.
 */
class AssessmentController extends Controller
{
  /**
   * @param  AssessmentAttemptService  $attemptService  Gestion des tentatives
   * @param  ChapterProgressService  $progressService  Accès aux chapitres
   * @param  ModuleExitQuizService  $moduleExitQuizService  Quiz fin de module M5
   */
  public function __construct(
    private readonly AssessmentAttemptService $attemptService,
    private readonly AssessmentReadinessService $readinessService,
    private readonly ChapterProgressService $progressService,
    private readonly ModuleExitQuizService $moduleExitQuizService,
  ) {}

  /**
   * Affiche le test ou redirige vers une tentative en cours.
   */
  public function show(Request $request, Assessment $assessment): Response|RedirectResponse
  {
    $user = $request->user('member');

    if (! $this->canAccessAssessment($user, $assessment)) {
      return redirect()->route('dashboard')->with('error', 'Ce test n\'est pas accessible pour le moment.');
    }

    if ($this->isOnlineBlocked($user, $assessment)) {
      return redirect()
        ->route('dashboard')
        ->with('error', 'Les tests en ligne sont désactivés pour votre mode ECAP présentiel.');
    }

    $assessment->load(['questions.options', 'chapter.course.program', 'courseModule']);

    if (! $this->readinessService->isReady($assessment)) {
      $this->readinessService->notifyAdminsIfEmpty($assessment);

      return Inertia::render('Assessment/Show', [
        'assessment' => array_merge(
          $this->attemptService->summaryForUser($user, $assessment),
          [
            'is_module_exit_quiz' => (bool) $assessment->is_module_exit_quiz,
            'required_questions' => $this->readinessService->requiredQuestionCount($assessment),
          ],
        ),
        'chapterId' => $assessment->chapter_id,
        'chapterTitle' => $assessment->chapter?->title,
        'moduleName' => $assessment->courseModule?->name,
        'pendingQuestions' => true,
      ]);
    }

    $inProgress = AssessmentAttempt::query()
      ->where('assessment_id', $assessment->id)
      ->where('user_id', $user->id)
      ->where('status', 'in_progress')
      ->latest('started_at')
      ->first();

    if ($inProgress) {
      if ($this->attemptService->isAttemptExpired($inProgress)) {
        $expiredAttempt = $this->attemptService->expireAttempt($inProgress);

        return redirect()
          ->route('assessment.result', [$assessment, $expiredAttempt])
          ->with('error', 'Le temps imparti pour ce test est écoulé.');
      }

      return Inertia::render('Assessment/Take', [
        ...$this->attemptService->attemptPayload($inProgress),
        'chapterId' => $assessment->chapter_id,
        'isModuleExitQuiz' => (bool) $assessment->is_module_exit_quiz,
      ]);
    }

    return Inertia::render('Assessment/Show', [
      'assessment' => array_merge(
        $this->attemptService->summaryForUser($user, $assessment),
        [
          'is_module_exit_quiz' => (bool) $assessment->is_module_exit_quiz,
          'required_questions' => $assessment->questionQuota()
            ?? ($assessment->is_module_exit_quiz
              ? $this->moduleExitQuizService->requiredQuestionsFor($assessment)
              : null),
        ],
      ),
      'chapterId' => $assessment->chapter_id,
      'chapterTitle' => $assessment->chapter?->title,
      'moduleName' => $assessment->courseModule?->name,
    ]);
  }

  /**
   * Démarre une nouvelle tentative de test.
   */
  public function start(Request $request, Assessment $assessment): RedirectResponse
  {
    $user = $request->user('member');

    if ($this->isOnlineBlocked($user, $assessment)) {
      return back()->with('error', 'Les tests en ligne sont désactivés pour votre mode ECAP présentiel.');
    }

    if (! $this->canAccessAssessment($user, $assessment)) {
      return back()->with('error', 'Ce test n\'est pas accessible pour le moment.');
    }

    $assessment->loadCount('questions');

    if (! $this->readinessService->isReady($assessment)) {
      $this->readinessService->notifyAdminsIfEmpty($assessment);

      return back()->with(
        'error',
        'Les questions de ce quiz ne sont pas encore disponibles. Vous serez notifié dès qu\'elles le seront.',
      );
    }

    try {
      $this->attemptService->startAttempt($user, $assessment);
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    return redirect()->route('assessment.show', $assessment);
  }

  /**
   * Soumet les réponses d'une tentative.
   */
  public function submit(Request $request, Assessment $assessment, AssessmentAttempt $attempt): RedirectResponse
  {
    $user = $request->user('member');

    if ($attempt->user_id !== $user->id || $attempt->assessment_id !== $assessment->id) {
      abort(403);
    }

    if ($this->isOnlineBlocked($user, $assessment)) {
      return back()->with('error', 'Les tests en ligne sont désactivés pour votre mode ECAP présentiel.');
    }

    $validated = $request->validate([
      'answers' => ['required', 'array'],
      'answers.*.option_id' => ['nullable', 'integer'],
      'answers.*.text' => ['nullable', 'string', 'max:5000'],
    ]);

    $answers = [];

    foreach ($validated['answers'] as $questionId => $answer) {
      $answers[(int) $questionId] = $answer;
    }

    try {
      $attempt = $this->attemptService->submitAttempt($attempt, $answers);
    } catch (\RuntimeException $exception) {
      return back()->with('error', $exception->getMessage());
    }

    return redirect()->route('assessment.result', [$assessment, $attempt]);
  }

  /**
   * Affiche les résultats avec liens de révision (M5).
   */
  public function result(Request $request, Assessment $assessment, AssessmentAttempt $attempt): Response|RedirectResponse
  {
    $user = $request->user('member');

    if ($attempt->user_id !== $user->id || $attempt->assessment_id !== $assessment->id) {
      abort(403);
    }

    if ($attempt->submitted_at === null) {
      return redirect()->route('assessment.show', $assessment);
    }

    return Inertia::render('Assessment/Result', $this->attemptService->resultPayload($attempt));
  }

  /**
   * Vérifie l'accès au test (chapitre ou quiz M5).
   */
  private function canAccessAssessment($user, Assessment $assessment): bool
  {
    if ($assessment->is_module_exit_quiz) {
      return $this->moduleExitQuizService->canAttempt($user, $assessment)
        || $this->attemptService->hasPassed($user, $assessment);
    }

    $chapter = $assessment->chapter;

    if ($chapter instanceof Chapter) {
      return $this->progressService->canAccess($user, $chapter);
    }

    return false;
  }

  /**
   * Vérifie le blocage présentiel ECAP.
   */
  private function isOnlineBlocked($user, Assessment $assessment): bool
  {
    if ($assessment->is_module_exit_quiz && $assessment->course_module_id) {
      $chapter = Chapter::query()
        ->where('course_module_id', $assessment->course_module_id)
        ->where('is_published', true)
        ->orderBy('sort_order')
        ->first();

      if ($chapter) {
        return ! $this->progressService->canInteractOnline($user, $chapter);
      }
    }

    $chapter = $assessment->chapter;

    if ($chapter) {
      return ! $this->progressService->canInteractOnline($user, $chapter);
    }

    return false;
  }
}
