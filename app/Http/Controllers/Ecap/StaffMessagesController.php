<?php

namespace App\Http\Controllers\Ecap;

use App\Http\Controllers\Controller;
use App\Services\Ecap\EcapPrivateChatService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Page messagerie acteurs ECAP (superviseur / modérateur) — disposition type WhatsApp.
 */
class StaffMessagesController extends Controller
{
  /**
   * @param  EcapPrivateChatService  $chatService  Service chat privé
   */
  public function __construct(
    private readonly EcapPrivateChatService $chatService,
  ) {}

  /**
   * Liste des conversations et fil de messages actif.
   */
  public function index(Request $request): Response
  {
    $user = $request->user('member');

    if (! $this->chatService->isStaffChatActor($user)) {
      abort(403);
    }

    $inbox = $this->chatService->inboxPayloadForStaff($user);

    return Inertia::render('Ecap/StaffMessages', [
      'inbox' => $inbox ?? [
        'session_name' => null,
        'conversations' => [],
        'active_peer_id' => null,
        'messages' => [],
        'poll_url' => '/mon-espace/ecap/chat/messages',
        'send_url' => '/mon-espace/ecap/chat/messages',
      ],
    ]);
  }
}
