<?php

namespace App\Http\Controllers;

use App\Models\MentorAssignment;
use App\Services\Student\MentorPortalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API JSON pour le chat mentor / mentoré (polling et envoi).
 */
class MentorChatController extends Controller
{
  /**
   * @param  MentorPortalService  $mentorService  Service portail mentorat
   */
  public function __construct(
    private readonly MentorPortalService $mentorService,
  ) {}

  /**
   * Messages récents pour le mentoré (Métamorpho).
   */
  public function menteePoll(Request $request): JsonResponse
  {
    $user = $request->user('member');

    if (! $this->mentorService->canUseMentorChat($user)) {
      return response()->json(['error' => 'Chat non disponible'], 403);
    }

    $assignment = $this->mentorService->metamorphoAssignmentForMentee($user);

    if (! $assignment) {
      return response()->json(['messages' => []]);
    }

    $sinceId = (int) $request->query('since', 0);

    return response()->json([
      'messages' => $this->mentorService->messagesSince($assignment, $user, $sinceId),
    ]);
  }

  /**
   * Envoie un message mentoré → mentor (JSON).
   */
  public function menteeSend(Request $request): JsonResponse
  {
    $user = $request->user('member');

    if (! $this->mentorService->canUseMentorChat($user)) {
      return response()->json(['error' => 'Chat non disponible'], 403);
    }

    $assignment = $this->mentorService->metamorphoAssignmentForMentee($user);

    if (! $assignment) {
      return response()->json(['error' => 'Aucune assignation active'], 422);
    }

    $validated = $request->validate([
      'body' => ['required', 'string', 'max:5000'],
    ]);

    $message = $this->mentorService->sendMessage($assignment, $user, $validated['body']);

    return response()->json([
      'message' => [
        'id' => $message->id,
        'body' => $message->body,
        'is_mine' => true,
        'sender_name' => $user->name,
        'created_at' => $message->created_at?->format('d/m/Y H:i'),
      ],
    ]);
  }

  /**
   * Messages récents pour le mentor (fiche mentoré).
   */
  public function mentorPoll(Request $request, MentorAssignment $assignment): JsonResponse
  {
    $user = $request->user('member');

    if ($assignment->mentor_id !== $user->id) {
      abort(403);
    }

    $sinceId = (int) $request->query('since', 0);

    return response()->json([
      'messages' => $this->mentorService->messagesSince($assignment, $user, $sinceId),
    ]);
  }

  /**
   * Envoie un message mentor → mentoré (JSON).
   */
  public function mentorSend(Request $request, MentorAssignment $assignment): JsonResponse
  {
    $user = $request->user('member');

    if ($assignment->mentor_id !== $user->id) {
      abort(403);
    }

    $validated = $request->validate([
      'body' => ['required', 'string', 'max:5000'],
    ]);

    $message = $this->mentorService->sendMessage($assignment, $user, $validated['body']);

    return response()->json([
      'message' => [
        'id' => $message->id,
        'body' => $message->body,
        'is_mine' => true,
        'sender_name' => $user->name,
        'created_at' => $message->created_at?->format('d/m/Y H:i'),
      ],
    ]);
  }
}
