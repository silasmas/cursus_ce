<?php

namespace App\Services\Ecap;

use App\Enums\EcapVacationRole;
use App\Models\AcademicSession;
use App\Models\EcapStaffAssignment;
use App\Models\User;
use App\Services\Portal\PortalMailService;
use Illuminate\Support\Collection;

/**
 * Envoie les e-mails aux acteurs ECAP après affectation ou mise à jour.
 */
class EcapStaffAssignmentNotifier
{
  /**
   * @param  PortalMailService  $mailService  Envoi des e-mails portail
   */
  public function __construct(
    private readonly PortalMailService $mailService,
  ) {}

  /**
   * Notifie par e-mail après création d'une ou plusieurs affectations.
   *
   * @param  Collection<int, EcapStaffAssignment>|array<int, EcapStaffAssignment>  $assignments
   */
  public function notifyCreated(Collection|array $assignments): void
  {
    $this->notifyGrouped($assignments, 'Nouvelle affectation ECAP');
  }

  /**
   * Notifie par e-mail après mise à jour d'une affectation.
   */
  public function notifyUpdated(EcapStaffAssignment $assignment): void
  {
    $this->notifyGrouped(collect([$assignment]), 'Affectation ECAP mise à jour');
  }

  /**
   * Regroupe par utilisateur et envoie un e-mail récapitulatif.
   *
   * @param  Collection<int, EcapStaffAssignment>|array<int, EcapStaffAssignment>  $assignments
   */
  private function notifyGrouped(Collection|array $assignments, string $mailSubjectPrefix): void
  {
    $rows = $assignments instanceof Collection ? $assignments : collect($assignments);

    if ($rows->isEmpty()) {
      return;
    }

    foreach ($rows as $assignment) {
      if ($assignment instanceof EcapStaffAssignment) {
        $assignment->loadMissing(['user', 'academicSession', 'courseModule', 'sessionVacation']);
      }
    }

    foreach ($rows->groupBy('user_id') as $userAssignments) {
      /** @var EcapStaffAssignment $first */
      $first = $userAssignments->first();
      $user = $first->user;

      if (! $user instanceof User) {
        continue;
      }

      $role = $first->role instanceof EcapVacationRole
        ? $first->role
        : EcapVacationRole::from($first->role);

      $sessionName = $first->academicSession?->name ?? 'Session ECAP';
      $moduleLines = $userAssignments
        ->map(fn (EcapStaffAssignment $row) => $row->courseModule?->name)
        ->filter()
        ->unique(fn (?string $name): string => $name ?? '')
        ->values();

      $body = $this->buildBody($role, $sessionName, $first->academicSession, $moduleLines, $first);

      $this->mailService->sendToUser(
        $user,
        $mailSubjectPrefix.' — '.$role->label(),
        $mailSubjectPrefix,
        $body,
        url('/ecap/acteurs/questions'),
        'Ouvrir l\'espace acteurs ECAP',
      );
    }
  }

  /**
   * Construit le corps du message selon le rôle et les modules.
   *
   * @param  Collection<int, string>  $moduleLines
   */
  private function buildBody(
    EcapVacationRole $role,
    string $sessionName,
    ?AcademicSession $session,
    Collection $moduleLines,
    EcapStaffAssignment $assignment,
  ): string {
    $lines = [
      'Bonjour,',
      '',
      'Vous avez été affecté(e) comme '.$role->label().' pour la session '.$sessionName.'.',
    ];

    if ($moduleLines->isNotEmpty()) {
      $lines[] = '';
      $lines[] = 'Module(s) concerné(s) :';
      foreach ($moduleLines as $moduleName) {
        $lines[] = '• '.$moduleName;
      }
    } elseif ($assignment->sessionVacation) {
      $lines[] = '';
      $lines[] = 'Vacation : '.$assignment->sessionVacation->name;
    } else {
      $lines[] = '';
      $lines[] = 'Périmètre : ensemble de la session.';
    }

    if (filled($assignment->notes)) {
      $lines[] = '';
      $lines[] = 'Note interne : '.$assignment->notes;
    }

    $lines[] = '';
    $lines[] = 'Connectez-vous à l\'espace acteurs ECAP pour consulter vos missions (questions, TP, méditation selon votre rôle).';

    if ($session?->starts_on) {
      $lines[] = '';
      $lines[] = 'Calendrier fidèle : Mon espace → Calendrier ECAP.';
    }

    return implode("\n", $lines);
  }
}
