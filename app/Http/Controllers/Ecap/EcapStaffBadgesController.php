<?php

namespace App\Http\Controllers\Ecap;

use App\Models\EcapStaffAssignment;
use App\Http\Controllers\Controller;
use App\Services\Ecap\EcapQuizGradingService;
use App\Services\Ecap\EcapStaffRoleService;
use App\Services\Student\VacationQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Compteurs temps réel pour le menu acteurs ECAP.
 */
class EcapStaffBadgesController extends Controller
{
  /**
   * @param  VacationQuestionService  $questionService  Q&R en attente
   * @param  EcapQuizGradingService  $quizGradingService  Quiz à corriger
   * @param  EcapStaffRoleService  $roleService  Habilitations acteur
   */
  public function __construct(
    private readonly VacationQuestionService $questionService,
    private readonly EcapQuizGradingService $quizGradingService,
    private readonly EcapStaffRoleService $roleService,
  ) {}

  /**
   * Retourne les badges du menu acteurs (polling).
   */
  public function index(Request $request): JsonResponse
  {
    $user = $request->user('member');

    if ($user === null) {
      return response()->json(['message' => 'Non authentifié.'], 401);
    }

    $pendingQuestions = 0;
    $pendingQuizGrading = 0;

    $isEcapStaff = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->exists();

    if ($isEcapStaff) {
      $pendingQuestions = $this->questionService->pendingCountForStaff($user);
    }

    if ($this->roleService->canGradeQuizzes($user)) {
      $pendingQuizGrading = $this->quizGradingService->pendingCountForStaff($user);
    }

    return response()->json([
      'ecapStaffPendingQuestions' => $pendingQuestions,
      'ecapStaffPendingQuizGrading' => $pendingQuizGrading,
    ]);
  }
}
