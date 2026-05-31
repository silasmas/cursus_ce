<?php

namespace App\Services\Mentor;

use App\Enums\PortalNotificationType;
use App\Models\MentorAssignment;
use App\Models\MentoringReport;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Support\Collection;

/**
 * Clôture d'accompagnement mentor — rapport à l'administration et avis mentoré.
 */
class MentorAssignmentClosureService
{
  /**
   * @param  PortalNotificationService  $notificationService  Notifications portail
   * @param  AdminNotificationService  $adminNotificationService  Notifications admin
   */
  public function __construct(
    private readonly PortalNotificationService $notificationService,
    private readonly AdminNotificationService $adminNotificationService,
  ) {}

  /**
   * Clôture un ou plusieurs accompagnements avec rapport pour l'administration.
   *
   * @param  array<int>  $assignmentIds
   * @return Collection<int, MentorAssignment>
   */
  public function closeForMany(User $mentor, array $assignmentIds, string $reportBody): Collection
  {
    $assignments = MentorAssignment::query()
      ->where('mentor_id', $mentor->id)
      ->whereIn('id', $assignmentIds)
      ->where('status', 'active')
      ->with(['mentee', 'program'])
      ->get();

    if ($assignments->isEmpty()) {
      throw new \RuntimeException('Aucun accompagnement actif sélectionné.');
    }

    $body = trim($reportBody);

    if (mb_strlen($body) < 20) {
      throw new \RuntimeException('Le rapport doit contenir au moins 20 caractères.');
    }

    return $assignments->map(function (MentorAssignment $assignment) use ($mentor, $body): MentorAssignment {
      return $this->closeAssignment($mentor, $assignment, $body);
    });
  }

  /**
   * Clôture une assignation et enregistre le rapport mentor.
   */
  public function closeAssignment(User $mentor, MentorAssignment $assignment, string $reportBody): MentorAssignment
  {
    if ($assignment->mentor_id !== $mentor->id) {
      throw new \RuntimeException('Assignation non autorisée.');
    }

    if ($assignment->status !== 'active') {
      throw new \RuntimeException('Cet accompagnement est déjà clôturé.');
    }

    MentoringReport::query()->create([
      'mentor_assignment_id' => $assignment->id,
      'report_kind' => 'closure',
      'author_id' => $mentor->id,
      'body' => trim($reportBody),
      'submitted_at' => now(),
    ]);

    $assignment->update([
      'status' => 'closed',
      'ended_at' => now(),
    ]);

    $mentee = $assignment->mentee;

    if ($mentee) {
      $this->notificationService->notifyWithEmail(
        $mentee,
        PortalNotificationType::AdminMessage,
        'Accompagnement clôturé',
        'Votre mentor '.$mentor->name.' a clôturé votre accompagnement'
          .($assignment->program?->name ? ' ('.$assignment->program->name.')' : '')
          .'. Vous pouvez laisser votre avis sur cette expérience.',
        '/mon-espace/mentor',
        'Donner mon avis',
        ['assignment_id' => $assignment->id],
        'Accompagnement clôturé — PHILA-CE',
      );
    }

    $this->adminNotificationService->notifyAdmins(
      'Rapport de clôture mentor',
      $mentor->name.' a clôturé l\'accompagnement de '
        .($mentee?->name ?? 'un mentoré')
        .' ('.($assignment->program?->name ?? 'programme').') et a soumis un rapport de clôture.',
      url('/admin/mentoring-reports'),
    );

    return $assignment->fresh(['mentee', 'program']);
  }
}
