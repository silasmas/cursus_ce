<?php

namespace App\Http\Controllers\Ecap;

use App\Enums\EcapVacationRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecap\Concerns\RespondsWithEcapAccessDenied;
use App\Models\AssessmentAttempt;
use App\Services\Ecap\EcapQuizGradingNotifier;
use App\Services\Ecap\EcapQuizGradingService;
use App\Services\Ecap\EcapStaffRoleService;
use App\Services\Student\AssessmentAttemptGradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Correction des quiz avec réponses rédigées (acteurs ECAP).
 */
class StaffQuizGradingController extends Controller
{
  use RespondsWithEcapAccessDenied;

  /**
   * @param  EcapQuizGradingService  $quizGradingService  File d'attente quiz
   * @param  AssessmentAttemptGradingService  $gradingService  Verrou et notation
   * @param  EcapQuizGradingNotifier  $notifier  Notifications fidèle
   * @param  EcapStaffRoleService  $roleService  Rôles acteurs
   */
  public function __construct(
    private readonly EcapQuizGradingService $quizGradingService,
    private readonly AssessmentAttemptGradingService $gradingService,
    private readonly EcapQuizGradingNotifier $notifier,
    private readonly EcapStaffRoleService $roleService,
  ) {}

  /**
   * Liste des quiz en attente de correction.
   */
  public function index(Request $request): Response|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Corrections quiz')) {
      return $denied;
    }

    return Inertia::render('Ecap/StaffQuizGrading', [
      'attempts' => $this->quizGradingService->pendingAttemptsForStaff($user),
      'historyAttempts' => $this->quizGradingService->gradedAttemptsForStaff($user),
      'graderScope' => $this->quizGradingService->graderScopeLabel($user),
      'feedUrl' => route('ecap.staff.quiz-grading.feed'),
    ]);
  }

  /**
   * Flux JSON des listes en attente et historique (polling).
   */
  public function feed(Request $request): JsonResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Corrections quiz')) {
      if ($denied instanceof JsonResponse) {
        return $denied;
      }

      abort(403);
    }

    return response()->json([
      'attempts' => $this->quizGradingService->pendingAttemptsForStaff($user),
      'historyAttempts' => $this->quizGradingService->gradedAttemptsForStaff($user),
    ]);
  }

  /**
   * Flux JSON d'une tentative en correction (polling).
   */
  public function attemptFeed(Request $request, AssessmentAttempt $attempt): JsonResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Correction d\'un quiz')) {
      if ($denied instanceof JsonResponse) {
        return $denied;
      }

      abort(403);
    }

    if (! $this->gradingService->canUserGrade($user, $attempt)) {
      abort(403);
    }

    return response()->json([
      'attempt' => $this->gradingService->gradingPayload($attempt->fresh(), $user),
    ]);
  }

  /**
   * Écran de correction d'une tentative (avec verrou).
   */
  public function show(Request $request, AssessmentAttempt $attempt): Response|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Correction d\'un quiz')) {
      return $denied;
    }

    if (! $this->gradingService->canUserGrade($user, $attempt)) {
      return $this->ecapAccessDeniedResponse(
        $request,
        $user,
        EcapVacationRole::Supervisor,
        'Correction d\'un quiz',
      );
    }

    $lockAcquired = false;

    if ($this->gradingService->needsManualGrading($attempt) && $this->gradingService->canEditAttempt($user, $attempt)) {
      $lockResult = $this->gradingService->acquireLock($user, $attempt);
      $lockAcquired = $lockResult['acquired'];
    }

    return Inertia::render('Ecap/StaffQuizGradingShow', [
      'attempt' => $this->gradingService->gradingPayload($attempt->fresh(), $user),
      'lock_acquired' => $lockAcquired,
      'feedUrl' => route('ecap.staff.quiz-grading.attempt-feed', $attempt),
    ]);
  }

  /**
   * Améliore une correction déjà enregistrée (correcteur initial).
   */
  public function updateGraded(Request $request, AssessmentAttempt $attempt): RedirectResponse|JsonResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Correction d\'un quiz')) {
      return $denied;
    }

    $validated = $request->validate([
      'grades' => ['required', 'array', 'min:1'],
      'grades.*.answer_id' => ['required', 'integer', 'exists:attempt_answers,id'],
      'grades.*.points_awarded' => ['required', 'numeric', 'min:0'],
      'grades.*.grader_feedback' => ['nullable', 'string', 'max:5000'],
    ]);

    try {
      $this->gradingService->updateGradedAnswers($user, $attempt, $validated['grades']);
    } catch (ValidationException $exception) {
      if ($request->wantsJson()) {
        return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
      }

      return redirect()
        ->route('ecap.staff.quiz-grading.show', $attempt)
        ->with('error', collect($exception->errors())->flatten()->first());
    }

    if ($request->wantsJson()) {
      return response()->json([
        'attempt' => $this->gradingService->gradingPayload($attempt->fresh(), $user),
      ]);
    }

    return redirect()
      ->route('ecap.staff.quiz-grading.show', $attempt)
      ->with('status', 'Correction mise à jour.');
  }

  /**
   * Publie un avis sur une correction déjà enregistrée.
   */
  public function storeComment(Request $request, AssessmentAttempt $attempt): RedirectResponse|JsonResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Correction d\'un quiz')) {
      return $denied;
    }

    $validated = $request->validate([
      'body' => ['required', 'string', 'max:5000'],
    ]);

    try {
      $this->gradingService->addComment($user, $attempt, $validated['body']);
    } catch (ValidationException $exception) {
      if ($request->wantsJson()) {
        return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
      }

      return redirect()
        ->route('ecap.staff.quiz-grading.show', $attempt)
        ->with('error', collect($exception->errors())->flatten()->first());
    }

    if ($request->wantsJson()) {
      return response()->json([
        'attempt' => $this->gradingService->gradingPayload($attempt->fresh(), $user),
      ]);
    }

    return redirect()
      ->route('ecap.staff.quiz-grading.show', $attempt)
      ->with('status', 'Avis publié.');
  }

  /**
   * Enregistre les notes des réponses rédigées.
   */
  public function grade(Request $request, AssessmentAttempt $attempt): RedirectResponse|JsonResponse|HttpResponse
  {
    $user = $request->user('member');

    if ($denied = $this->denyUnlessQuizGrader($request, $user, 'Correction d\'un quiz')) {
      return $denied;
    }

    $validated = $request->validate([
      'grades' => ['required', 'array', 'min:1'],
      'grades.*.answer_id' => ['required', 'integer', 'exists:attempt_answers,id'],
      'grades.*.points_awarded' => ['required', 'numeric', 'min:0'],
      'grades.*.grader_feedback' => ['nullable', 'string', 'max:5000'],
    ]);

    try {
      $graded = $this->gradingService->gradeWrittenAnswers($user, $attempt, $validated['grades']);
      $this->notifier->notifyStudentGraded($graded);
    } catch (ValidationException $exception) {
      if ($request->wantsJson()) {
        return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
      }

      return redirect()
        ->route('ecap.staff.quiz-grading.show', $attempt)
        ->with('error', collect($exception->errors())->flatten()->first());
    }

    if ($request->wantsJson()) {
      return response()->json([
        'attempt' => $this->gradingService->gradingPayload($graded->fresh(), $user),
      ]);
    }

    return redirect()
      ->route('ecap.staff.quiz-grading.show', $graded)
      ->with('status', 'Correction enregistrée. Le fidèle a été notifié.');
  }

  /**
   * Libère le verrou de correction (navigation ou fermeture).
   */
  public function releaseLock(Request $request, AssessmentAttempt $attempt): JsonResponse
  {
    $user = $request->user('member');

    if ($user === null) {
      return response()->json(['ok' => false], 401);
    }

    $this->gradingService->releaseLock($user, $attempt);

    return response()->json(['ok' => true]);
  }

  /**
   * Refuse l'accès si l'utilisateur n'est pas acteur ECAP habilité à corriger.
   */
  private function denyUnlessQuizGrader(Request $request, $user, string $featureLabel): ?HttpResponse
  {
    if ($user === null) {
      abort(401);
    }

    if ($this->roleService->canGradeQuizzes($user)) {
      return null;
    }

    return $this->ecapAccessDeniedResponse($request, $user, EcapVacationRole::Supervisor, $featureLabel);
  }
}
