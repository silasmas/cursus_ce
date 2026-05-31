<?php

namespace App\Filament\Resources\EcapStaffAssignments\Pages;

use App\Enums\EcapVacationRole;
use App\Filament\Concerns\SendsFilamentOperationFeedback;
use App\Filament\Resources\EcapStaffAssignments\EcapStaffAssignmentResource;
use App\Filament\Resources\Pages\EditRecord;
use App\Services\Ecap\EcapStaffAssignmentNotifier;
use App\Services\Ecap\EcapStaffAssignmentValidator;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Édition d'une affectation acteur vacation ECAP.
 */
class EditEcapStaffAssignment extends EditRecord
{
  use SendsFilamentOperationFeedback;

  protected static string $resource = EcapStaffAssignmentResource::class;

  /**
   * Désactive le toast générique Filament (remplacé par afterSave).
   */
  protected function getSavedNotificationTitle(): ?string
  {
    return null;
  }

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make()
        ->successNotification(null)
        ->after(function (): void {
          $this->sendFilamentFeedback(
            Notification::make()
              ->title('Affectation supprimée')
              ->success()
              ->body('L\'utilisateur n\'est plus affecté à ce rôle ECAP.'),
          );
        }),
    ];
  }

  /**
   * Valide les règles de cumul de rôles avant sauvegarde.
   *
   * @param  array<string, mixed>  $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeSave(array $data): array
  {
    $role = EcapVacationRole::from($data['role']);

    if (in_array($role, [EcapVacationRole::Teacher, EcapVacationRole::Supervisor], true) && empty($data['course_module_id'])) {
      throw ValidationException::withMessages([
        'course_module_id' => 'Le module de cours est obligatoire pour un enseignant ou un superviseur.',
      ]);
    }

    app(EcapStaffAssignmentValidator::class)->assertCanAssign(
      (int) $data['user_id'],
      (int) $data['academic_session_id'],
      $role,
      (int) $this->record->getKey(),
      filled($data['course_module_id'] ?? null) ? (int) $data['course_module_id'] : null,
    );

    return $data;
  }

  /**
   * Notification après mise à jour réussie.
   */
  protected function afterSave(): void
  {
    $role = $this->record->role instanceof EcapVacationRole
      ? $this->record->role
      : EcapVacationRole::from($this->record->role);

    $this->record->refresh();

    app(EcapStaffAssignmentNotifier::class)->notifyUpdated($this->record);

    $moduleLabel = $this->record->courseModule?->name;
    $moduleSuffix = $moduleLabel ? " — module {$moduleLabel}" : '';

    $this->sendFilamentFeedback(
      Notification::make()
        ->title('Affectation mise à jour')
        ->success()
        ->body(
          $this->record->user?->name.' — rôle '.$role->label().$moduleSuffix
          .' (actif : '.($this->record->is_active ? 'oui' : 'non').'). E-mail envoyé.',
        ),
    );
  }
}
