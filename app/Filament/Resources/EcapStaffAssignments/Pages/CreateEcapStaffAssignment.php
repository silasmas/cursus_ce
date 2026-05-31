<?php

namespace App\Filament\Resources\EcapStaffAssignments\Pages;

use App\Enums\EcapVacationRole;
use App\Filament\Concerns\SendsFilamentOperationFeedback;
use App\Filament\Resources\EcapStaffAssignments\EcapStaffAssignmentResource;
use App\Filament\Resources\Pages\CreateRecord;
use App\Models\CourseModule;
use App\Models\EcapStaffAssignment;
use App\Services\Ecap\EcapStaffAssignmentNotifier;
use App\Services\Ecap\EcapStaffAssignmentValidator;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

/**
 * Création d'affectations acteurs vacation ECAP (multi-utilisateurs, multi-modules).
 */
class CreateEcapStaffAssignment extends CreateRecord
{
  use SendsFilamentOperationFeedback;

  protected static string $resource = EcapStaffAssignmentResource::class;

  /**
   * @var array<int, int>
   */
  private array $userIds = [];

  /**
   * @var array<int, int|null>
   */
  private array $moduleIds = [];

  /**
   * @var array<int, EcapStaffAssignment>
   */
  private array $createdAssignments = [];

  /**
   * @var array<int, string>
   */
  private array $createdSummaryLines = [];

  /**
   * @var array<int, string>
   */
  private array $failureMessages = [];

  /**
   * Désactive le toast générique Filament (remplacé par afterCreate).
   */
  protected function getCreatedNotificationTitle(): ?string
  {
    return null;
  }

  /**
   * Prépare les identifiants utilisateurs et modules avant création.
   *
   * @param  array<string, mixed>  $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $this->userIds = array_values(array_unique(array_map('intval', $data['user_ids'] ?? [])));

    if ($this->userIds === []) {
      throw ValidationException::withMessages([
        'user_ids' => 'Sélectionnez au moins un utilisateur.',
      ]);
    }

    $role = EcapVacationRole::from($data['role']);
    $requiresModule = in_array($role, [EcapVacationRole::Teacher, EcapVacationRole::Supervisor], true);

    if ($requiresModule) {
      $this->moduleIds = array_values(array_unique(array_map('intval', $data['course_module_ids'] ?? [])));

      if ($this->moduleIds === []) {
        throw ValidationException::withMessages([
          'course_module_ids' => 'Sélectionnez au moins un module pour un enseignant ou un superviseur.',
        ]);
      }
    } else {
      $this->moduleIds = [null];
    }

    $validator = app(EcapStaffAssignmentValidator::class);

    foreach ($this->userIds as $userId) {
      foreach ($this->moduleIds as $moduleId) {
        $validator->assertCanAssign(
          $userId,
          (int) $data['academic_session_id'],
          $role,
          courseModuleId: $moduleId,
        );
      }
    }

    unset($data['user_ids'], $data['course_module_ids']);

    $data['user_id'] = $this->userIds[0];
    $data['course_module_id'] = $this->moduleIds[0];

    return $data;
  }

  /**
   * Crée une affectation par utilisateur et par module sélectionné.
   *
   * @param  array<string, mixed>  $data
   */
  protected function handleRecordCreation(array $data): Model
  {
    $role = EcapVacationRole::from($data['role']);
    $last = null;
    $this->failureMessages = [];
    $this->createdAssignments = [];
    $this->createdSummaryLines = [];

    $moduleNames = CourseModule::query()
      ->whereIn('id', array_filter($this->moduleIds))
      ->pluck('name', 'id');

    foreach ($this->userIds as $userId) {
      foreach ($this->moduleIds as $moduleId) {
        try {
          $last = EcapStaffAssignment::query()->create([
            ...$data,
            'user_id' => $userId,
            'course_module_id' => $moduleId,
          ]);

          $this->createdAssignments[] = $last;

          $userName = $last->user?->name ?? "Utilisateur #{$userId}";
          $moduleLabel = $moduleId ? ($moduleNames[$moduleId] ?? "module #{$moduleId}") : 'toute la session';
          $this->createdSummaryLines[] = "{$userName} — {$moduleLabel}";
        } catch (QueryException) {
          $moduleLabel = $moduleId ? ($moduleNames[$moduleId] ?? "module #{$moduleId}") : 'session';
          $this->failureMessages[] = "Utilisateur #{$userId} / {$moduleLabel} : doublon ou contrainte.";
        }
      }
    }

    if ($last === null) {
      throw ValidationException::withMessages([
        'user_ids' => 'Aucune affectation n\'a pu être créée. Vérifiez les doublons et les règles de cumul de rôles.',
      ]);
    }

    return $last;
  }

  /**
   * Toasts, e-mails et notifications cloche après création.
   */
  protected function afterCreate(): void
  {
    $role = $this->record->role instanceof EcapVacationRole
      ? $this->record->role
      : EcapVacationRole::from($this->record->role);

    if ($this->createdAssignments !== []) {
      app(EcapStaffAssignmentNotifier::class)->notifyCreated($this->createdAssignments);

      $count = count($this->createdAssignments);
      $body = $count > 1
        ? "{$count} affectations créées ({$role->label()}). E-mail envoyé à chaque personne concernée.\n".implode("\n", array_slice($this->createdSummaryLines, 0, 8))
        : $this->createdSummaryLines[0].' est maintenant '.$role->label().'. E-mail de confirmation envoyé.';

      if (count($this->createdSummaryLines) > 8) {
        $body .= "\n… et ".(count($this->createdSummaryLines) - 8).' autre(s).';
      }

      $this->sendFilamentFeedback(
        Notification::make()
          ->title('Affectation enregistrée')
          ->success()
          ->body($body),
      );
    }

    if ($this->failureMessages !== []) {
      $this->sendFilamentFeedback(
        Notification::make()
          ->title('Certaines affectations ont échoué')
          ->warning()
          ->body(implode("\n", $this->failureMessages))
          ->persistent(),
      );
    }
  }

  /**
   * Redirige vers la liste après création.
   */
  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
