<?php

namespace App\Services\Ecap;

use App\Enums\EcapVacationRole;
use App\Support\UserPresentation;
use App\Enums\PortalNotificationType;
use App\Models\ChapterProgress;
use App\Models\EcapDirectMessage;
use App\Models\EcapStaffAssignment;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\AcademicSession;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use App\Services\Student\FormationJourneyService;
use Illuminate\Support\Collection;

/**
 * Chat privé fidèle ECAP ↔ superviseur / modérateur.
 */
class EcapPrivateChatService
{
  /**
   * @param  VacationQuestionService  $vacationQuestionService  Session ECAP du fidèle
   * @param  EcapPeriodAccessService  $periodAccessService  Session via inscription
   * @param  FormationJourneyService  $formationJourneyService  Parcours ECAP
   */
  public function __construct(
    private readonly VacationQuestionService $vacationQuestionService,
    private readonly EcapPeriodAccessService $periodAccessService,
    private readonly FormationJourneyService $formationJourneyService,
    private readonly PortalNotificationService $portalNotificationService,
  ) {}

  /**
   * Indique si le chat privé est disponible (session ECAP + au moins une étape commencée ou terminée).
   */
  public function isEnabledForUser(User $user): bool
  {
    if ($this->isStaffChatActorInternal($user)) {
      return $this->sessionForChatUser($user) !== null;
    }

    if ($this->sessionForChatUser($user) === null) {
      return false;
    }

    $enrollment = $this->resolveEnrollment($user);

    if ($enrollment !== null) {
      $hasProgress = ChapterProgress::query()
        ->where('enrollment_id', $enrollment->id)
        ->where(function ($query) {
          $query->whereNotNull('completed_at')
            ->orWhereNotNull('last_content_block_id');
        })
        ->exists();

      if ($hasProgress) {
        return true;
      }
    }

    return $this->hasStartedEcapStep($user);
  }

  /**
   * Payload Inertia pour le bouton flottant (fidèle).
   *
   * @return array<string, mixed>|null
   */
  public function payloadForUser(User $user): ?array
  {
    if ($this->isStaffChatActor($user)) {
      return null;
    }

    if (! $this->isEnabledForUser($user)) {
      return null;
    }

    $session = $this->sessionForChatUser($user);

    if ($session === null) {
      return null;
    }

    $contacts = $this->contactsWithUnreadForUser($user, $session->id);
    $peerId = $contacts[0]['id'] ?? null;

    return [
      'enabled' => true,
      'contacts' => $contacts,
      'contacts_empty' => $contacts === [],
      'initial_peer_id' => $peerId,
      'initial_messages' => $peerId
        ? $this->messagesWithUser($user, $session->id, $peerId)
        : [],
      'poll_url' => '/mon-espace/ecap/chat/messages',
      'send_url' => '/mon-espace/ecap/chat/messages',
      'unread_url' => '/mon-espace/ecap/chat/unread',
      'unread_count' => $this->unreadCountForUser($user),
    ];
  }

  /**
   * Payload page messagerie acteur (liste + conversation active).
   *
   * @return array<string, mixed>|null
   */
  public function inboxPayloadForStaff(User $actor): ?array
  {
    if (! $this->isStaffChatActor($actor)) {
      return null;
    }

    $session = $this->sessionForChatUser($actor);

    if ($session === null) {
      return null;
    }

    $conversations = $this->conversationsForActor($actor, $session->id);
    $activePeerId = request()->integer('peer') ?: ($conversations[0]['id'] ?? null);

    if ($activePeerId) {
      $this->markConversationRead($actor, $session->id, $activePeerId);
    }

    return [
      'session_name' => $session->name,
      'conversations' => $conversations,
      'active_peer_id' => $activePeerId,
      'messages' => $activePeerId
        ? $this->messagesWithUser($actor, $session->id, $activePeerId)
        : [],
      'poll_url' => '/mon-espace/ecap/chat/messages',
      'send_url' => '/mon-espace/ecap/chat/messages',
      'unread_url' => '/mon-espace/ecap/chat/unread',
    ];
  }

  /**
   * Session de chat ECAP pour un utilisateur (fidèle ou acteur).
   */
  public function sessionForChatUser(User $user): ?AcademicSession
  {
    $studentSession = $this->resolveSession($user);

    if ($studentSession !== null) {
      return $studentSession;
    }

    if (! $this->isStaffChatActor($user)) {
      return null;
    }

    $sessionId = EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->whereIn('role', [
        EcapVacationRole::Supervisor->value,
        EcapVacationRole::Moderator->value,
      ])
      ->orderByDesc('academic_session_id')
      ->value('academic_session_id');

    if ($sessionId === null) {
      return null;
    }

    return AcademicSession::query()->find($sessionId);
  }

  /**
   * Messages entre deux utilisateurs dans une session.
   *
   * @return array<int, array<string, mixed>>
   */
  public function messagesWithUser(
    User $viewer,
    int $sessionId,
    int $peerUserId,
    int $sinceId = 0,
  ): array {
    return $this->conversationQuery($viewer->id, $peerUserId)
      ->when($sinceId > 0, fn ($query) => $query->where('id', '>', $sinceId))
      ->with(['sender.profile', 'recipient.profile'])
      ->orderBy('id')
      ->limit(200)
      ->get()
      ->map(fn (EcapDirectMessage $message) => $this->mapMessage($message, $viewer))
      ->values()
      ->all();
  }

  /**
   * Liste des conversations pour un acteur (style WhatsApp).
   *
   * @return array<int, array<string, mixed>>
   */
  public function conversationsForActor(User $actor, int $sessionId): array
  {
    $peerIds = $this->peerIdsForActor($actor, $sessionId);

    if ($peerIds->isEmpty()) {
      return [];
    }

    return User::query()
      ->with('profile')
      ->whereIn('id', $peerIds)
      ->get()
      ->map(fn (User $peer) => $this->mapConversation($actor, $peer, $sessionId))
      ->sortByDesc('last_message_at_sort')
      ->values()
      ->all();
  }

  /**
   * Session ECAP du fidèle (profil ou inscription).
   */
  private function resolveSession(User $user): ?AcademicSession
  {
    return $this->vacationQuestionService->studentSession($user)
      ?? $this->periodAccessService->userEcapSession($user);
  }

  /**
   * Inscription ECAP la plus pertinente.
   */
  private function resolveEnrollment(User $user): ?Enrollment
  {
    $session = $this->resolveSession($user);

    if ($session === null) {
      return Enrollment::query()
        ->where('user_id', $user->id)
        ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
        ->latest('enrolled_at')
        ->first();
    }

    return Enrollment::query()
      ->where('user_id', $user->id)
      ->where(function ($query) use ($session) {
        $query->where('academic_session_id', $session->id)
          ->orWhereHas('program', fn ($inner) => $inner->where('slug', 'ecap'));
      })
      ->latest('enrolled_at')
      ->first();
  }

  /**
   * Au moins une étape ECAP disponible, en cours ou terminée.
   */
  private function hasStartedEcapStep(User $user): bool
  {
    $program = Program::query()->where('slug', 'ecap')->where('is_active', true)->first();

    if ($program === null) {
      return false;
    }

    $journey = $this->formationJourneyService->forProgram($user, $program);

    return collect($journey['steps'] ?? [])
      ->contains(fn (array $step) => in_array($step['status'] ?? '', ['completed', 'in_progress', 'available'], true));
  }

  /**
   * Superviseurs et modérateurs contactables (fidèle).
   *
   * @return array<int, array<string, mixed>>
   */
  public function contactsForSession(int $sessionId): array
  {
    return EcapStaffAssignment::query()
      ->where('academic_session_id', $sessionId)
      ->where('is_active', true)
      ->whereIn('role', [
        EcapVacationRole::Supervisor->value,
        EcapVacationRole::Moderator->value,
      ])
      ->with('user.profile')
      ->get()
      ->map(fn (EcapStaffAssignment $row) => [
        'id' => $row->user_id,
        'name' => $row->user?->name,
        'role' => $row->role instanceof EcapVacationRole ? $row->role->label() : EcapVacationRole::from($row->role)->label(),
        'avatar_url' => $this->avatarUrlForUser($row->user),
      ])
      ->unique('id')
      ->values()
      ->all();
  }

  /**
   * Contacts chat selon profil utilisateur.
   *
   * @return array<int, array<string, mixed>>
   */
  public function contactsForUser(User $user, int $sessionId): array
  {
    if ($this->isStaffChatActor($user)) {
      return $this->contactsForActor($user, $sessionId);
    }

    return $this->contactsForSession($sessionId);
  }

  /**
   * Messages récents du fil privé (toutes conversations — legacy).
   *
   * @return array<int, array<string, mixed>>
   */
  public function recentMessages(User $user, int $sessionId, int $sinceId = 0): array
  {
    return EcapDirectMessage::query()
      ->where('academic_session_id', $sessionId)
      ->where(function ($query) use ($user) {
        $query->where('sender_user_id', $user->id)
          ->orWhere('recipient_user_id', $user->id);
      })
      ->when($sinceId > 0, fn ($query) => $query->where('id', '>', $sinceId))
      ->with(['sender.profile', 'recipient.profile'])
      ->orderBy('id')
      ->limit(50)
      ->get()
      ->map(fn (EcapDirectMessage $message) => $this->mapMessage($message, $user))
      ->values()
      ->all();
  }

  /**
   * Envoie un message privé.
   */
  public function send(
    User $sender,
    int $recipientUserId,
    string $body,
    string $subjectContext = 'general',
    ?int $subjectId = null,
  ): EcapDirectMessage {
    $session = $this->sessionForChatUser($sender);

    if ($session === null) {
      abort(403);
    }

    if (! $this->isStaffChatActor($sender) && ! $this->isEnabledForUser($sender)) {
      abort(403);
    }

    $allowedRecipient = $this->isStaffChatActor($sender)
      ? $this->isStudentInSession($recipientUserId, $session->id)
      : EcapStaffAssignment::query()
        ->where('academic_session_id', $session->id)
        ->where('user_id', $recipientUserId)
        ->where('is_active', true)
        ->whereIn('role', [
          EcapVacationRole::Supervisor->value,
          EcapVacationRole::Moderator->value,
        ])
        ->exists();

    if (! $allowedRecipient) {
      abort(422, 'Destinataire non autorisé.');
    }

    $sessionId = $this->resolveConversationSessionId($sender, $recipientUserId) ?? $session->id;

    $message = EcapDirectMessage::query()->create([
      'academic_session_id' => $sessionId,
      'sender_user_id' => $sender->id,
      'recipient_user_id' => $recipientUserId,
      'subject_context' => $subjectContext,
      'subject_id' => $subjectId,
      'body' => $body,
      'read_at' => null,
    ]);

    $recipient = User::query()->find($recipientUserId);

    if ($recipient !== null) {
      try {
        $actionUrl = $this->isStaffChatActor($recipient)
          ? '/ecap/acteurs/messages?peer='.$sender->id
          : '/mon-espace/ecap/messages?peer='.$sender->id;

        $this->portalNotificationService->notifyWithEmail(
          $recipient,
          PortalNotificationType::AdminMessage,
          'Nouveau message ECAP',
          $sender->name.' : "'.mb_strimwidth($body, 0, 120, '...').'"',
          $actionUrl,
          'Ouvrir la conversation',
        );
      } catch (\Throwable) {
        // Le message reste enregistré même si la notification échoue.
      }
    }

    return $message;
  }

  /**
   * Marque les messages reçus comme lus.
   */
  public function markConversationRead(User $viewer, int $sessionId, int $peerUserId): void
  {
    $this->conversationQuery($viewer->id, $peerUserId)
      ->where('sender_user_id', $peerUserId)
      ->where('recipient_user_id', $viewer->id)
      ->whereNull('read_at')
      ->update(['read_at' => now()]);
  }

  /**
   * @return Collection<int, int>
   */
  private function peerIdsForActor(User $actor, int $sessionId): Collection
  {
    $enrolledUserIds = Enrollment::query()
      ->where('academic_session_id', $sessionId)
      ->pluck('user_id');

    $conversationUserIds = EcapDirectMessage::query()
      ->where('academic_session_id', $sessionId)
      ->where(function ($query) use ($actor) {
        $query->where('sender_user_id', $actor->id)
          ->orWhere('recipient_user_id', $actor->id);
      })
      ->get(['sender_user_id', 'recipient_user_id'])
      ->flatMap(fn (EcapDirectMessage $row) => [$row->sender_user_id, $row->recipient_user_id]);

    return $enrolledUserIds
      ->merge($conversationUserIds)
      ->unique()
      ->filter(fn (int $id) => $id !== $actor->id)
      ->values();
  }

  /**
   * @return array<string, mixed>
   */
  private function mapConversation(User $actor, User $peer, int $sessionId): array
  {
    $lastMessage = $this->conversationQuery($actor->id, $peer->id)
      ->latest('id')
      ->first();

    $unreadCount = $this->conversationQuery($actor->id, $peer->id)
      ->where('sender_user_id', $peer->id)
      ->where('recipient_user_id', $actor->id)
      ->whereNull('read_at')
      ->count();

        return [
      'id' => $peer->id,
      'name' => $peer->name,
      'role' => 'Fidèle',
      'avatar_url' => $this->avatarUrlForUser($peer),
      'last_message' => $lastMessage?->body ?? '',
      'last_message_at' => $lastMessage?->created_at?->format('d/m/Y H:i') ?? '',
      'last_message_mine' => $lastMessage && (int) $lastMessage->sender_user_id === (int) $actor->id,
      'last_message_is_read' => $lastMessage?->read_at !== null,
      'unread_count' => $unreadCount,
      'has_unread' => $unreadCount > 0,
      'last_message_at_sort' => $lastMessage?->created_at?->timestamp ?? 0,
    ];
  }

  /**
   * Représentation JSON d'un message pour un utilisateur donné.
   *
   * @return array<string, mixed>
   */
  public function mapMessageForViewer(EcapDirectMessage $message, User $viewer): array
  {
    return $this->mapMessage($message, $viewer);
  }

  /**
   * @return array<string, mixed>
   */
  private function mapMessage(EcapDirectMessage $message, User $viewer): array
  {
    $senderPresentation = UserPresentation::for($message->sender);

    return [
      'id' => $message->id,
      'body' => $message->body,
      'sender_user_id' => (int) $message->sender_user_id,
      'is_mine' => (int) $message->sender_user_id === (int) $viewer->id,
      'sender_name' => $senderPresentation['name'],
      'sender_avatar_url' => $senderPresentation['avatar_url'],
      'sender_initials' => $senderPresentation['initials'],
      'recipient_name' => $message->recipient?->name,
      'created_at' => $message->created_at?->format('d/m/Y H:i'),
      'created_at_time' => $message->created_at?->format('H:i') ?? '',
      'is_read' => $message->read_at !== null,
      'read_at' => $message->read_at?->format('d/m/Y H:i'),
    ];
  }

  private function avatarUrlForUser(?User $user): ?string
  {
    return UserPresentation::for($user)['avatar_url'];
  }

  /**
   * Nombre total de messages non lus pour un fidèle.
   */
  public function unreadCountForUser(User $user): int
  {
    $session = $this->sessionForChatUser($user);

    if ($session === null) {
      return 0;
    }

    return EcapDirectMessage::query()
      ->where('academic_session_id', $session->id)
      ->where('recipient_user_id', $user->id)
      ->whereNull('read_at')
      ->count();
  }

  /**
   * Payload JSON pour le polling des non-lus.
   *
   * @return array{unread_count: int, contacts: array<int, array<string, mixed>>}
   */
  public function unreadPayloadForUser(User $user): array
  {
    $session = $this->sessionForChatUser($user);

    if ($session === null) {
      return ['unread_count' => 0, 'contacts' => []];
    }

    if ($this->isStaffChatActor($user)) {
      return [
        'unread_count' => $this->unreadCountForActor($user),
        'contacts' => $this->conversationsForActor($user, $session->id),
      ];
    }

    return [
      'unread_count' => $this->unreadCountForUser($user),
      'contacts' => $this->contactsWithUnreadForUser($user, $session->id),
    ];
  }

  /**
   * Marque toutes les conversations comme lues pour l'utilisateur.
   */
  public function markAllRead(User $user): int
  {
    $session = $this->sessionForChatUser($user);

    if ($session === null) {
      return 0;
    }

    return EcapDirectMessage::query()
      ->where('academic_session_id', $session->id)
      ->where('recipient_user_id', $user->id)
      ->whereNull('read_at')
      ->update(['read_at' => now()]);
  }

  /**
   * Contacts fidèle avec compteur non-lus par acteur.
   *
   * @return array<int, array<string, mixed>>
   */
  private function contactsWithUnreadForUser(User $user, int $sessionId): array
  {
    return collect($this->contactsForSession($sessionId))
      ->map(function (array $contact) use ($user) {
        $unreadCount = $this->conversationQuery($user->id, (int) $contact['id'])
          ->where('sender_user_id', $contact['id'])
          ->where('recipient_user_id', $user->id)
          ->whereNull('read_at')
          ->count();

        return [
          ...$contact,
          'unread_count' => $unreadCount,
        ];
      })
      ->values()
      ->all();
  }

  /**
   * Nombre total de messages non lus pour un acteur.
   */
  public function unreadCountForActor(User $actor): int
  {
    $session = $this->sessionForChatUser($actor);

    if ($session === null) {
      return 0;
    }

    return EcapDirectMessage::query()
      ->where('academic_session_id', $session->id)
      ->where('recipient_user_id', $actor->id)
      ->whereNull('read_at')
      ->count();
  }

  /**
   * Indique si l'utilisateur peut utiliser la messagerie acteur (superviseur / modérateur).
   */
  public function isStaffChatActor(User $user): bool
  {
    return $this->isStaffChatActorInternal($user);
  }

  private function isStaffChatActorInternal(User $user): bool
  {
    return EcapStaffAssignment::query()
      ->where('user_id', $user->id)
      ->where('is_active', true)
      ->whereIn('role', [
        EcapVacationRole::Supervisor->value,
        EcapVacationRole::Moderator->value,
      ])
      ->exists();
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function contactsForActor(User $actor, int $sessionId): array
  {
    $peerIds = $this->peerIdsForActor($actor, $sessionId);

    if ($peerIds->isEmpty()) {
      return [];
    }

    return User::query()
      ->with('profile')
      ->whereIn('id', $peerIds)
      ->orderBy('name')
      ->get()
      ->map(fn (User $user) => [
        'id' => $user->id,
        'name' => $user->name,
        'role' => 'Fidèle',
        'avatar_url' => $this->avatarUrlForUser($user),
      ])
      ->values()
      ->all();
  }

  private function isStudentInSession(int $userId, int $sessionId): bool
  {
    if (\App\Models\Profile::query()
      ->where('user_id', $userId)
      ->where('academic_session_id', $sessionId)
      ->exists()) {
      return true;
    }

    return Enrollment::query()
      ->where('user_id', $userId)
      ->where('academic_session_id', $sessionId)
      ->exists();
  }

  /**
   * Requête de base pour une conversation 1-à-1 (toutes sessions confondues).
   */
  private function conversationQuery(int $userId, int $peerUserId): \Illuminate\Database\Eloquent\Builder
  {
    return EcapDirectMessage::query()
      ->where(function ($query) use ($userId, $peerUserId) {
        $query->where(function ($inner) use ($userId, $peerUserId) {
          $inner->where('sender_user_id', $userId)
            ->where('recipient_user_id', $peerUserId);
        })->orWhere(function ($inner) use ($userId, $peerUserId) {
          $inner->where('sender_user_id', $peerUserId)
            ->where('recipient_user_id', $userId);
        });
      });
  }

  /**
   * Session ECAP à utiliser pour une nouvelle conversation.
   */
  private function resolveConversationSessionId(User $sender, int $recipientUserId): ?int
  {
    $existing = $this->conversationQuery($sender->id, $recipientUserId)
      ->latest('id')
      ->value('academic_session_id');

    if ($existing !== null) {
      return (int) $existing;
    }

    $recipient = User::query()->find($recipientUserId);
    $recipientSession = $recipient ? $this->sessionForChatUser($recipient) : null;

    if ($recipientSession !== null) {
      return $recipientSession->id;
    }

    return $this->sessionForChatUser($sender)?->id;
  }

}
