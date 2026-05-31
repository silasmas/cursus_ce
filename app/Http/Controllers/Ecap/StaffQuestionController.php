<?php

namespace App\Http\Controllers\Ecap;

use App\Enums\VacationQuestionReplyType;
use App\Http\Controllers\Controller;
use App\Models\VacationQuestion;
use App\Models\VacationQuestionReply;
use App\Services\Ecap\VacationQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Fil Q&R ECAP pour les acteurs (enseignants) par module de cours.
 */
class StaffQuestionController extends Controller
{
  /**
   * @param  VacationQuestionService  $vacationQuestionService  Service Q&R vacation
   */
  public function __construct(
    private readonly VacationQuestionService $vacationQuestionService,
  ) {}

  /**
   * Fil des questions du module sélectionné.
   */
  public function index(Request $request): Response
  {
    return Inertia::render(
      'Ecap/StaffQuestions',
      $this->vacationQuestionService->staffPortalPayload(
        $request->user('member'),
        $request->integer('module') ?: null,
        $request->integer('addressee') ?: null,
        $request->integer('author') ?: null,
      ),
    );
  }

  /**
   * Posts du fil uniquement (JSON).
   */
  public function feed(Request $request): JsonResponse
  {
    return response()->json(
      $this->vacationQuestionService->staffFeedPosts(
        $request->user('member'),
        $request->integer('module') ?: null,
        $request->integer('addressee') ?: null,
        $request->integer('author') ?: null,
      ),
    );
  }

  /**
   * Publie une réponse officielle ou un avis sur une question.
   */
  public function reply(Request $request, VacationQuestion $question): RedirectResponse|JsonResponse
  {
    $this->authorizeAccess($request, $question);

    $validated = $request->validate([
      'body' => ['required', 'string', 'max:10000'],
      'reply_type' => ['nullable', 'string', 'in:answer,comment'],
      'parent_reply_id' => ['nullable', 'integer', 'exists:vacation_question_replies,id'],
    ]);

    $replyType = ($validated['reply_type'] ?? 'answer') === 'comment'
      ? VacationQuestionReplyType::Comment
      : VacationQuestionReplyType::Answer;

    $this->vacationQuestionService->reply(
      $question,
      $request->user('member'),
      $validated['body'],
      $replyType,
      isset($validated['parent_reply_id']) ? (int) $validated['parent_reply_id'] : null,
    );

    if ($request->wantsJson()) {
      return response()->json(
        $this->vacationQuestionService->staffFeedPosts(
          $request->user('member'),
          $question->course_module_id,
          $request->integer('addressee') ?: null,
          $request->integer('author') ?: null,
        ),
      );
    }

    return redirect()
      ->route('ecap.staff.questions.index', ['module' => $question->course_module_id])
      ->with('status', $replyType === VacationQuestionReplyType::Comment ? 'Avis publié.' : 'Réponse publiée.');
  }

  /**
   * Modifie une réponse officielle (remplace le texte visible).
   */
  public function updateReply(Request $request, VacationQuestionReply $reply): RedirectResponse|JsonResponse
  {
    $reply->loadMissing('question');
    $this->authorizeAccess($request, $reply->question);

    $validated = $request->validate([
      'body' => ['required', 'string', 'max:10000'],
    ]);

    $this->vacationQuestionService->updateReply(
      $reply,
      $request->user('member'),
      $validated['body'],
    );

    if ($request->wantsJson()) {
      return response()->json(
        $this->vacationQuestionService->staffFeedPosts(
          $request->user('member'),
          $reply->question?->course_module_id,
          $request->integer('addressee') ?: null,
          $request->integer('author') ?: null,
        ),
      );
    }

    return redirect()
      ->route('ecap.staff.questions.index', ['module' => $reply->question?->course_module_id])
      ->with('status', 'Réponse mise à jour.');
  }

  /**
   * Bascule le pouce sur une réponse (lecture seule côté staff si besoin).
   */
  public function toggleLike(Request $request, VacationQuestionReply $reply): JsonResponse
  {
    $this->authorizeAccess($request, $reply->question);

    return response()->json(
      $this->vacationQuestionService->toggleReplyLike($request->user('member'), $reply),
    );
  }

  /**
   * Vérifie l'accès lecture au fil.
   */
  private function authorizeAccess(Request $request, VacationQuestion $question): void
  {
    if (! $this->vacationQuestionService->canView($request->user('member'), $question)) {
      abort(403);
    }
  }
}
