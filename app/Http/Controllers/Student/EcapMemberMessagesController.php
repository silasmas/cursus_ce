<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\Ecap\EcapPrivateChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page messagerie fidèle ECAP (conversation avec superviseur / modérateur).
 */
class EcapMemberMessagesController extends Controller
{
  /**
   * @param  EcapPrivateChatService  $chatService  Service chat privé
   */
  public function __construct(
    private readonly EcapPrivateChatService $chatService,
  ) {}

  /**
   * Affiche la messagerie plein écran pour le fidèle.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');

    if (! $this->chatService->isEnabledForUser($user)) {
      abort(403);
    }

    $session = $this->chatService->sessionForChatUser($user);

    if ($session === null) {
      abort(403);
    }

    $contacts = $this->chatService->contactsForUser($user, $session->id);
    $activePeerId = $request->integer('peer') ?: ($contacts[0]['id'] ?? null);

    return Inertia::render('Ecap/MemberMessages', [
      'contacts' => $contacts,
      'contacts_empty' => $contacts === [],
      'active_peer_id' => $activePeerId,
      'messages' => $activePeerId
        ? $this->chatService->messagesWithUser($user, $session->id, $activePeerId)
        : [],
      'poll_url' => '/mon-espace/ecap/chat/messages',
      'send_url' => '/mon-espace/ecap/chat/messages',
    ]);
  }
}
