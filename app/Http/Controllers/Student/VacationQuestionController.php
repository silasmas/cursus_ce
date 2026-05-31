<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\VacationQuestionReply;
use App\Services\Ecap\VacationQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Fil Q&R ECAP côté fidèle (style publication Facebook).
 */
class VacationQuestionController extends Controller
{
  /**
   * @param  VacationQuestionService  $vacationQuestionService  Service Q&R vacation
   */
  public function __construct(
    private readonly VacationQuestionService $vacationQuestionService,
  ) {}

  /**
   * Fil des questions par module de cours ECAP.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');

    return Inertia::render(
      'Ecap/VacationQuestions',
      $this->vacationQuestionService->studentPortalPayload(
        $user,
        $request->integer('module') ?: null,
        $request->integer('addressee') ?: null,
        $request->integer('author') ?: null,
      ),
    );
  }

  /**
   * Retourne uniquement les posts (sans recharger la page).
   */
  public function feed(Request $request): JsonResponse
  {
    $user = $request->user('member');

    return response()->json(
      $this->vacationQuestionService->studentFeedPosts(
        $user,
        $request->integer('module') ?: null,
        $request->integer('addressee') ?: null,
        $request->integer('author') ?: null,
      ),
    );
  }

  /**
   * Publie une nouvelle question (@prof, @tous par défaut, #module).
   */
  public function store(Request $request): RedirectResponse
  {
    $validated = $request->validate([
      'course_module_id' => ['required', 'integer', 'exists:course_modules,id'],
      'body' => ['required', 'string', 'max:5000'],
      'address_all_teachers' => ['nullable'],
      'addressed_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
    ]);

    $addressAllTeachers = ! $request->filled('address_all_teachers')
      || filter_var($validated['address_all_teachers'] ?? true, FILTER_VALIDATE_BOOLEAN);

    $this->vacationQuestionService->ask(
      $request->user('member'),
      (int) $validated['course_module_id'],
      $validated['body'],
      $addressAllTeachers,
      $addressAllTeachers ? null : ($validated['addressed_to_user_id'] ?? null),
    );

    return redirect()
      ->route('ecap.questions.index', ['module' => $validated['course_module_id']])
      ->with('status', 'Votre question a été publiée. Tous les acteurs ECAP peuvent la voir.');
  }

  /**
   * Bascule le pouce sur une réponse.
   */
  public function toggleLike(Request $request, VacationQuestionReply $reply): JsonResponse
  {
    $question = $reply->question;

    if (! $this->vacationQuestionService->canView($request->user('member'), $question)) {
      abort(403);
    }

    return response()->json(
      $this->vacationQuestionService->toggleReplyLike($request->user('member'), $reply),
    );
  }
}
