<?php

namespace App\Services\Mentor;

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentMenteeResponse;
use App\Enums\PortalNotificationType;
use App\Models\MentorAppointment;
use App\Models\MentorAssignment;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Programmation et gestion de rendez-vous en ligne mentor / mentoré.
 */
class MentorAppointmentService
{
  private const MEETING_DURATION_MINUTES = 60;

  /**
   * @param  PortalNotificationService  $notificationService  Notifications portail
   * @param  AdminNotificationService  $adminNotificationService  Notifications admin
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
    private readonly AdminNotificationService $adminNotificationService,
  ) {}

  /**
   * Crée un rendez-vous pour un mentoré.
   */
  public function scheduleForAssignment(
    User $mentor,
    MentorAssignment $assignment,
    string $scheduledAt,
    AppointmentChannel $channel,
    ?string $meetingUrl,
    ?string $notes,
  ): MentorAppointment {
    if ($assignment->mentor_id !== $mentor->id) {
      throw new \RuntimeException('Assignation invalide.');
    }

    $appointment = MentorAppointment::query()->create([
      'mentor_id' => $mentor->id,
      'mentor_assignment_id' => $assignment->id,
      'scheduled_at' => Carbon::parse($scheduledAt),
      'channel' => $channel->value,
      'meeting_url' => $meetingUrl,
      'notes' => $notes,
      'status' => 'scheduled',
      'mentee_response' => AppointmentMenteeResponse::Pending->value,
    ]);

    $this->notifyMenteeOfNewAppointment($appointment, $channel);

    return $appointment;
  }

  /**
   * Met à jour un rendez-vous programmé par le mentor.
   */
  public function updateAppointment(
    User $mentor,
    MentorAppointment $appointment,
    string $scheduledAt,
    AppointmentChannel $channel,
    ?string $meetingUrl,
    ?string $notes,
  ): MentorAppointment {
    if ($appointment->mentor_id !== $mentor->id) {
      throw new \RuntimeException('Rendez-vous non autorisé.');
    }

    if ($this->computePhase($appointment) === 'past') {
      throw new \RuntimeException('Impossible de modifier un rendez-vous passé.');
    }

    $appointment->update([
      'scheduled_at' => Carbon::parse($scheduledAt),
      'channel' => $channel->value,
      'meeting_url' => $meetingUrl,
      'notes' => $notes,
      'mentee_response' => AppointmentMenteeResponse::Pending->value,
      'responded_at' => null,
      'proposed_reschedule_at' => null,
      'response_note' => null,
    ]);

    $mentee = $appointment->mentorAssignment?->mentee;

    if ($mentee) {
      $this->notificationService->notify(
        $mentee,
        PortalNotificationType::MeetingReminder,
        'Rendez-vous modifié',
        'Votre mentor a modifié le rendez-vous du '.$appointment->scheduled_at->format('d/m/Y à H:i').'. Merci de confirmer à nouveau.',
        '/mon-espace/mentor',
        'Voir et répondre',
        ['appointment_id' => $appointment->id],
      );
    }

    return $appointment->fresh();
  }

  /**
   * Programme le même créneau pour plusieurs mentorés.
   *
   * @param  array<int>  $assignmentIds
   * @return Collection<int, MentorAppointment>
   */
  public function scheduleForMany(
    User $mentor,
    array $assignmentIds,
    string $scheduledAt,
    AppointmentChannel $channel,
    ?string $meetingUrl,
    ?string $notes,
  ): Collection {
    $assignments = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->whereIn('id', $assignmentIds)
      ->where('status', 'active')
      ->get();

    return $assignments->map(fn (MentorAssignment $assignment) => $this->scheduleForAssignment(
      $mentor,
      $assignment,
      $scheduledAt,
      $channel,
      $meetingUrl,
      $notes,
    ));
  }

  /**
   * Enregistre la réponse du mentoré à un rendez-vous.
   */
  public function recordMenteeResponse(
    User $mentee,
    MentorAppointment $appointment,
    AppointmentMenteeResponse $response,
    ?string $proposedRescheduleAt,
    ?string $responseNote,
  ): MentorAppointment {
    $assignment = $appointment->mentorAssignment;

    if (! $assignment || $assignment->mentee_id !== $mentee->id) {
      throw new \RuntimeException('Rendez-vous non autorisé.');
    }

    if ($this->computePhase($appointment) === 'past') {
      throw new \RuntimeException('Ce rendez-vous est déjà passé.');
    }

    if ($response === AppointmentMenteeResponse::Postponed && ! $proposedRescheduleAt) {
      throw new \RuntimeException('Indiquez une date proposée pour le report.');
    }

    $appointment->update([
      'mentee_response' => $response->value,
      'proposed_reschedule_at' => $proposedRescheduleAt ? Carbon::parse($proposedRescheduleAt) : null,
      'response_note' => $responseNote,
      'responded_at' => now(),
    ]);

    $mentor = $appointment->mentor;

    if ($mentor) {
      $body = match ($response) {
        AppointmentMenteeResponse::Accepted => $mentee->name.' a accepté le rendez-vous du '.$appointment->scheduled_at->format('d/m/Y à H:i').'.',
        AppointmentMenteeResponse::Declined => $mentee->name.' a refusé le rendez-vous du '.$appointment->scheduled_at->format('d/m/Y à H:i').'.',
        AppointmentMenteeResponse::Postponed => $mentee->name.' souhaite reporter le rendez-vous.'
          .($appointment->proposed_reschedule_at ? ' Nouvelle date proposée : '.$appointment->proposed_reschedule_at->format('d/m/Y à H:i').'.' : ''),
        default => $mentee->name.' a répondu au rendez-vous.',
      };

      $this->notificationService->notify(
        $mentor,
        PortalNotificationType::MeetingReminder,
        'Réponse au rendez-vous — '.$response->label(),
        $body,
        '/mentor/mentore/'.$assignment->id,
        'Voir la fiche',
        ['appointment_id' => $appointment->id, 'response' => $response->value],
      );
    }

    return $appointment->fresh();
  }

  /**
   * Rendez-vous à venir pour un mentor.
   *
   * @return Collection<int, MentorAppointment>
   */
  public function upcomingForMentor(User $mentor): Collection
  {
    return MentorAppointment::query()
      ->where('mentor_id', $mentor->id)
      ->where('scheduled_at', '>=', now()->subDays(7))
      ->where('status', 'scheduled')
      ->with(['mentorAssignment.mentee', 'mentorAssignment.program'])
      ->orderBy('scheduled_at')
      ->get();
  }

  /**
   * Historique rendez-vous pour un mentoré.
   *
   * @return Collection<int, MentorAppointment>
   */
  public function historyForMentee(User $mentee): Collection
  {
    return MentorAppointment::query()
      ->whereHas('mentorAssignment', fn ($q) => $q->where('mentee_id', $mentee->id)->where('status', 'active'))
      ->where('scheduled_at', '>=', now()->subDays(30))
      ->where('status', 'scheduled')
      ->with(['mentor', 'mentorAssignment.program'])
      ->orderBy('scheduled_at')
      ->get();
  }

  /**
   * Rendez-vous à venir pour un mentoré.
   *
   * @return Collection<int, MentorAppointment>
   */
  public function upcomingForMentee(User $mentee): Collection
  {
    return $this->historyForMentee($mentee);
  }

  /**
   * Rendez-vous d'une assignation.
   *
   * @return Collection<int, MentorAppointment>
   */
  public function forAssignment(MentorAssignment $assignment): Collection
  {
    return MentorAppointment::query()
      ->where('mentor_assignment_id', $assignment->id)
      ->where('scheduled_at', '>=', now()->subDays(30))
      ->where('status', 'scheduled')
      ->orderBy('scheduled_at')
      ->get();
  }

  /**
   * Payload rendez-vous pour l'interface.
   *
   * @return array<string, mixed>
   */
  public function payload(MentorAppointment $appointment): array
  {
    $channel = $appointment->channel instanceof AppointmentChannel
      ? $appointment->channel
      : AppointmentChannel::from($appointment->channel);

    $response = $appointment->mentee_response instanceof AppointmentMenteeResponse
      ? $appointment->mentee_response
      : AppointmentMenteeResponse::from($appointment->mentee_response ?? 'pending');

    return [
      'id' => $appointment->id,
      'scheduled_at' => $appointment->scheduled_at?->format('d/m/Y H:i'),
      'scheduled_at_iso' => $appointment->scheduled_at?->toIso8601String(),
      'channel' => $channel->value,
      'channel_label' => $channel->label(),
      'meeting_url' => $appointment->meeting_url,
      'notes' => $appointment->notes,
      'mentee_name' => $appointment->mentorAssignment?->mentee?->name,
      'program' => $appointment->mentorAssignment?->program?->name,
      'mentee_response' => $response->value,
      'mentee_response_label' => $response->label(),
      'proposed_reschedule_at_iso' => $appointment->proposed_reschedule_at?->toIso8601String(),
      'response_note' => $appointment->response_note,
      'phase' => $this->computePhase($appointment),
      'can_edit' => $this->computePhase($appointment) !== 'past',
    ];
  }

  /**
   * Phase temporelle du rendez-vous.
   */
  public function computePhase(MentorAppointment $appointment): string
  {
    $start = $appointment->scheduled_at;
    $end = $start?->copy()->addMinutes(self::MEETING_DURATION_MINUTES);
    $now = now();

    if (! $start || ! $end) {
      return 'future';
    }

    if ($now->gt($end)) {
      return 'past';
    }

    if ($now->gte($start) && $now->lte($end)) {
      return 'ongoing';
    }

    if ($now->lt($start) && $now->diffInHours($start) < 24) {
      return 'soon';
    }

    return 'future';
  }

  /**
   * Notifie le mentoré d'un nouveau rendez-vous.
   */
  private function notifyMenteeOfNewAppointment(MentorAppointment $appointment, AppointmentChannel $channel): void
  {
    $mentee = $appointment->mentorAssignment?->mentee;

    if (! $mentee) {
      return;
    }

    $this->notificationService->notify(
      $mentee,
      PortalNotificationType::MeetingReminder,
      'Rendez-vous avec votre mentor',
      'Votre mentor a programmé un rendez-vous '.$channel->label().' le '.$appointment->scheduled_at->format('d/m/Y à H:i').'. Merci de confirmer votre disponibilité.',
      '/mon-espace/mentor',
      'Répondre',
      ['appointment_id' => $appointment->id, 'channel' => $channel->value],
    );
  }
}
