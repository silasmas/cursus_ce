<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentQuizHistoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Historique des quiz passés par le fidèle.
 */
class QuizHistoryController extends Controller
{
  /**
   * @param  StudentQuizHistoryService  $historyService  Agrégation des tentatives
   */
  public function __construct(
    private readonly StudentQuizHistoryService $historyService,
  ) {}

  /**
   * Liste des quiz passés, en attente ou corrigés.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');
    $attempts = $this->historyService->attemptsForUser($user);

    return Inertia::render('Assessment/History', [
      'attempts' => $attempts,
      'pendingCount' => collect($attempts)->where('is_pending_grading', true)->count(),
      'gradedCount' => collect($attempts)->where('is_pending_grading', false)->count(),
    ]);
  }
}
