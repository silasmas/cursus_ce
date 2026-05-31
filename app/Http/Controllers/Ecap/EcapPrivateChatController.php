<?php

namespace App\Http\Controllers\Ecap;

use App\Http\Controllers\Controller;
use App\Services\Ecap\EcapPrivateChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API JSON chat privé fidèle ↔ acteurs ECAP.
 */
class EcapPrivateChatController extends Controller
{
  /**
   * @param  EcapPrivateChatService  $chatService  Service chat privé
   */
  public function __construct(
    private readonly EcapPrivateChatService $chatService,
  ) {}

  /**
   * Compteur messages non lus (polling hors page chat).
   */
  public function unread(Request $request): JsonResponse
  {
    $user = $request->user('member');

    return response()->json(
      $this->chatService->unreadPayloadForUser($user),
    );
  }

  /**
   * Marque tous les messages reçus comme lus.
   */
  public function markAllRead(Request $request): JsonResponse
  {
    $user = $request->user('member');
    $updated = $this->chatService->markAllRead($user);

    return response()->json([
      'marked' => $updated,
      ...$this->chatService->unreadPayloadForUser($user),
    ]);
  }

  /**
   * Messages récents (polling), filtrés par interlocuteur si peer fourni.
   */
  public function index(Request $request): JsonResponse
  {
    $user = $request->user('member');
    $session = $this->chatService->sessionForChatUser($user);

    if ($session === null) {
      return response()->json(['messages' => []]);
    }

    $sinceId = (int) $request->query('since', 0);
    $peerId = (int) $request->query('peer', 0);

    if ($peerId > 0) {
      $this->chatService->markConversationRead($user, $session->id, $peerId);

      return response()->json([
        'messages' => $this->chatService->messagesWithUser($user, $session->id, $peerId, $sinceId),
      ]);
    }

    return response()->json([
      'messages' => $this->chatService->recentMessages($user, $session->id, $sinceId),
      'contacts' => $this->chatService->contactsForUser($user, $session->id),
    ]);
  }

  /**
   * Envoie un message privé.
   */
  public function store(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'recipient_user_id' => ['required', 'integer', 'exists:users,id'],
      'body' => ['required', 'string', 'max:5000'],
      'subject_context' => ['nullable', 'string', 'max:32'],
      'subject_id' => ['nullable', 'integer'],
    ]);

    $sender = $request->user('member');

    $message = $this->chatService->send(
      $sender,
      (int) $validated['recipient_user_id'],
      $validated['body'],
      $validated['subject_context'] ?? 'general',
      $validated['subject_id'] ?? null,
    );

    $message->load(['sender', 'recipient']);

    return response()->json([
      'message' => $this->chatService->mapMessageForViewer($message, $sender),
    ]);
  }
}
