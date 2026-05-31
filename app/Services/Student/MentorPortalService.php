<?php

namespace App\Services\Student;

use App\Enums\PortalNotificationType;
use App\Support\UserPresentation;
use App\Models\MentorAssignment;
use App\Models\MentorMessage;
use App\Models\MentoringDecision;
use App\Models\MentoringFeedback;
use App\Models\Program;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Collection;

/**
 * Portail mentor / mentoré — profil, messages et avis Métamorpho.
 */
class MentorPortalService
{
  /**
   * @param  PortalNotificationService  $notificationService  Notifications in-app
   * @param  CursusProgressService  $cursusProgressService  Progression cursus
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
    private readonly CursusProgressService $cursusProgressService,
  ) {}

  /**
   * Assignation Métamorpho active du fidèle.
   */
  public function metamorphoAssignmentForMentee(User $mentee): ?MentorAssignment
  {
    $assignment = $this->assignmentForMenteePortal($mentee);

    return $assignment?->status === 'active' ? $assignment : null;
  }

  /**
   * Assignation Métamorpho pour le portail mentoré (active ou clôturée récemment).
   */
  public function assignmentForMenteePortal(User $mentee): ?MentorAssignment
  {
    return MentorAssignment::query()
      ->where('mentee_id', $mentee->id)
      ->whereIn('status', ['active', 'closed'])
      ->whereHas('program', fn ($query) => $query->where('slug', 'metamorpho'))
      ->with(['mentor.profile', 'mentor.mentorProfile', 'feedbacks', 'messages.sender'])
      ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
      ->latest('started_at')
      ->first();
  }

  /**
   * Indique si l'accompagnement est clôturé par le mentor.
   */
  public function isAccompanimentClosed(MentorAssignment $assignment): bool
  {
    return $assignment->status === 'closed';
  }

  /**
   * Le mentoré peut-il laisser un avis après clôture ?
   */
  public function canSubmitClosureFeedback(MentorAssignment $assignment, User $mentee): bool
  {
    if ($assignment->mentee_id !== $mentee->id || ! $this->isAccompanimentClosed($assignment)) {
      return false;
    }

    return ! $this->hasSubmittedClosureFeedback($assignment, $mentee);
  }

  /**
   * Avis de clôture déjà soumis par le mentoré.
   */
  public function hasSubmittedClosureFeedback(MentorAssignment $assignment, User $mentee): bool
  {
    return MentoringFeedback::query()
      ->where('mentor_assignment_id', $assignment->id)
      ->where('author_id', $mentee->id)
      ->where('feedback_type', 'mentee_closure_feedback')
      ->exists();
  }

  /**
   * Enregistre l'avis du mentoré après clôture de l'accompagnement.
   */
  public function submitMenteeClosureFeedback(
    MentorAssignment $assignment,
    User $mentee,
    int $rating,
    string $comment,
  ): MentoringFeedback {
    if (! $this->canSubmitClosureFeedback($assignment, $mentee)) {
      throw new \RuntimeException('Vous ne pouvez pas soumettre d\'avis pour le moment.');
    }

    return MentoringFeedback::query()->create([
      'mentor_assignment_id' => $assignment->id,
      'author_id' => $mentee->id,
      'body' => trim($comment),
      'rating' => max(1, min(5, $rating)),
      'feedback_type' => 'mentee_closure_feedback',
      'visible_to_mentor' => true,
    ]);
  }

  /**
   * Indique si le fidèle a accès au chat mentor (étape Métamorpho débloquée).
   */
  public function canUseMentorChat(User $user): bool
  {
    if ($this->metamorphoAssignmentForMentee($user)) {
      return true;
    }

    $cursus = $this->cursusProgressService->forUser($user);
    $metamorpho = collect($cursus['modules'])->firstWhere('slug', 'metamorpho');

    return $metamorpho !== null && ($metamorpho['status'] ?? 'locked') !== 'locked';
  }

  /**
   * Mentorés actifs d'un mentor.
   *
   * @return Collection<int, MentorAssignment>
   */
  public function activeMenteesForMentor(User $mentor): Collection
  {
    return MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->where('status', 'active')
      ->with(['mentee.profile', 'program', 'messages' => fn ($q) => $q->latest()->limit(1)])
      ->orderByDesc('started_at')
      ->get();
  }

  /**
   * Données publiques limitées du mentor pour le mentoré (nom, sexe, photo).
   *
   * @return array<string, mixed>|null
   */
  public function mentorProfilePayload(MentorAssignment $assignment): ?array
  {
    $mentor = $assignment->mentor;

    if (! $mentor) {
      return null;
    }

    $userProfile = $mentor->profile;
    $presentation = UserPresentation::for($mentor);

    return [
      'id' => $mentor->id,
      'name' => $mentor->name,
      'gender' => $this->formatGenre($userProfile?->genre),
      'avatar_url' => $presentation['avatar_url'],
      'initials' => $presentation['initials'],
      'assignment_id' => $assignment->id,
      'started_at' => $assignment->started_at?->format('d/m/Y'),
    ];
  }

  /**
   * Profil détaillé du mentoré visible par le mentor.
   *
   * @return array<string, mixed>
   */
  public function menteeProfilePayloadForMentor(User $mentee, ?MentorAssignment $assignment = null): array
  {
    $profile = $mentee->profile;
    $presentation = UserPresentation::for($mentee);

    return [
      'name' => $mentee->name,
      'email' => $mentee->email,
      'phone' => $profile?->phone,
      'gender' => $this->formatGenre($profile?->genre),
      'age' => $profile?->date_naissance?->age,
      'country' => $profile?->nationalite ?: $profile?->nationalite_autre,
      'avatar_url' => $presentation['avatar_url'],
      'initials' => $presentation['initials'],
      'program' => $assignment?->program?->name,
      'started_at' => $assignment?->started_at?->format('d/m/Y'),
    ];
  }

  /**
   * Formate le genre pour l'affichage.
   */
  public function formatGenre(?string $genre): ?string
  {
    return match ($genre) {
      'M' => 'Homme',
      'F' => 'Femme',
      default => $genre,
    };
  }

  /**
   * Initiales à partir du nom complet.
   */
  public function userInitials(?string $name): string
  {
    if (! $name) {
      return '?';
    }

    $parts = preg_split('/\s+/', trim($name)) ?: [];

    return strtoupper(collect($parts)->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
  }

  /**
   * Envoie un message mentor ↔ mentoré avec notification.
   */
  public function sendMessage(MentorAssignment $assignment, User $sender, string $body): MentorMessage
  {
    $message = MentorMessage::query()->create([
      'mentor_assignment_id' => $assignment->id,
      'sender_id' => $sender->id,
      'body' => trim($body),
    ]);

    $recipient = $sender->id === $assignment->mentor_id
      ? $assignment->mentee
      : $assignment->mentor;

    if ($recipient) {
      $isFromMentor = $sender->id === $assignment->mentor_id;

      $this->notificationService->notify(
        $recipient,
        $isFromMentor ? PortalNotificationType::MentorMessage : PortalNotificationType::MenteeMessage,
        $isFromMentor ? 'Message de votre mentor' : 'Message d\'un mentoré',
        \Illuminate\Support\Str::limit(trim($body), 120),
        $isFromMentor ? '/mon-espace/mentor' : '/mentor/mentore/'.$assignment->id,
        'Répondre',
        ['assignment_id' => $assignment->id, 'message_id' => $message->id],
      );
    }

    return $message;
  }

  /**
   * Enregistre le rapport / avis du mentoré après aval mentor pour passage de niveau.
   */
  public function submitMenteeFeedback(
    MentorAssignment $assignment,
    User $mentee,
    int $rating,
    string $comment,
  ): MentoringFeedback {
    if (! $this->canSubmitProgressReport($assignment, $mentee)) {
      throw new \RuntimeException('Le formulaire rapport n\'est pas encore débloqué. Attendez l\'aval de votre mentor.');
    }

    return MentoringFeedback::query()->create([
      'mentor_assignment_id' => $assignment->id,
      'author_id' => $mentee->id,
      'body' => trim($comment),
      'rating' => max(1, min(5, $rating)),
      'feedback_type' => 'mentee_progress_report',
      'visible_to_mentor' => true,
    ]);
  }

  /**
   * Indique si le mentoré peut soumettre son rapport de passage de niveau.
   */
  public function canSubmitProgressReport(MentorAssignment $assignment, User $mentee): bool
  {
    if ($assignment->mentee_id !== $mentee->id) {
      return false;
    }

    $latestApproval = MentoringDecision::query()
      ->where('mentor_assignment_id', $assignment->id)
      ->where('decision', 'approved')
      ->latest('decided_at')
      ->first();

    if (! $latestApproval) {
      return false;
    }

    $alreadySubmitted = MentoringFeedback::query()
      ->where('mentor_assignment_id', $assignment->id)
      ->where('author_id', $mentee->id)
      ->where('feedback_type', 'mentee_progress_report')
      ->where('created_at', '>=', $latestApproval->decided_at)
      ->exists();

    return ! $alreadySubmitted;
  }

  /**
   * Raison d'attente si le rapport n'est pas débloqué.
   */
  public function progressReportBlockReason(MentorAssignment $assignment, User $mentee): ?string
  {
    if ($this->canSubmitProgressReport($assignment, $mentee)) {
      return null;
    }

    $hasApproval = MentoringDecision::query()
      ->where('mentor_assignment_id', $assignment->id)
      ->where('decision', 'approved')
      ->exists();

    if (! $hasApproval) {
      return 'Votre mentor doit d\'abord valider votre progression (TP ou étape) pour débloquer ce rapport.';
    }

    return 'Vous avez déjà soumis votre rapport pour cette étape.';
  }

  /**
   * Vérifie que l'utilisateur participe à l'assignation.
   */
  public function userCanAccessAssignment(User $user, MentorAssignment $assignment): bool
  {
    return $assignment->mentor_id === $user->id
      || $assignment->mentee_id === $user->id;
  }

  /**
   * Messages formatés pour l'interface.
   *
   * @return array<int, array<string, mixed>>
   */
  public function messagesPayload(MentorAssignment $assignment, User $viewer): array
  {
    return $assignment->messages()
      ->with('sender')
      ->orderBy('created_at')
      ->get()
      ->map(fn (MentorMessage $message) => $this->messagePayload($message, $viewer))
      ->all();
  }

  /**
   * Nouveaux messages depuis un identifiant (polling).
   *
   * @return array<int, array<string, mixed>>
   */
  public function messagesSince(MentorAssignment $assignment, User $viewer, int $sinceId = 0): array
  {
    return $assignment->messages()
      ->with('sender')
      ->when($sinceId > 0, fn ($q) => $q->where('id', '>', $sinceId))
      ->orderBy('created_at')
      ->get()
      ->map(fn (MentorMessage $message) => $this->messagePayload($message, $viewer))
      ->all();
  }

  /**
   * Formate un message pour le frontend.
   *
   * @return array<string, mixed>
   */
  private function messagePayload(MentorMessage $message, User $viewer): array
  {
    return [
      'id' => $message->id,
      'body' => $message->body,
      'is_mine' => $message->sender_id === $viewer->id,
      'sender_name' => $message->sender?->name,
      'created_at' => $message->created_at?->format('d/m/Y H:i'),
    ];
  }

  /**
   * Indique si le mentoré a déjà laissé un rapport de progression.
   */
  public function hasSubmittedFinalFeedback(MentorAssignment $assignment, User $mentee): bool
  {
    return MentoringFeedback::query()
      ->where('mentor_assignment_id', $assignment->id)
      ->where('author_id', $mentee->id)
      ->where('feedback_type', 'mentee_progress_report')
      ->exists();
  }

  /**
   * Notifie le mentoré que le rapport est débloqué.
   */
  public function notifyReportUnlocked(MentorAssignment $assignment): void
  {
    $mentee = $assignment->mentee;

    if (! $mentee) {
      return;
    }

    $this->notificationService->notify(
      $mentee,
      PortalNotificationType::ReportUnlocked,
      'Rapport de progression débloqué',
      'Votre mentor a validé votre progression. Soumettez votre rapport pour passer au niveau suivant.',
      '/mon-espace/mentor',
      'Remplir le rapport',
      ['assignment_id' => $assignment->id],
    );
  }

  /**
   * Programme Métamorpho.
   */
  public function metamorphoProgram(): ?Program
  {
    return Program::query()->where('slug', 'metamorpho')->first();
  }
}
