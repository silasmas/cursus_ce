<?php

namespace App\Filament\Resources\AcademicSessions\Pages;

use App\Filament\Concerns\SendsFilamentOperationFeedback;
use App\Filament\Resources\AcademicSessions\AcademicSessionResource;
use App\Models\AcademicSession;
use App\Services\Ecap\DuplicateEcapSessionConfigurationService;
use App\Services\Ecap\EcapGenerationCodeService;
use App\Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

/**
 * Création d'une génération ECAP avec numéro auto-généré et duplication optionnelle.
 */
class CreateAcademicSession extends CreateRecord
{
  use SendsFilamentOperationFeedback;

  protected static string $resource = AcademicSessionResource::class;

  /**
   * Session source pour recopier la configuration (non persistée).
   */
  private ?int $duplicateFromSessionId = null;

  /**
   * Assigne le code ECAP aléatoire et le numéro ordinal avant création.
   *
   * @param  array<string, mixed>  $data
   * @return array<string, mixed>
   */
  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $generator = app(EcapGenerationCodeService::class);

    $data['code'] = $generator->generateCode();
    $data['generation_number'] = $generator->nextGenerationNumber();

    $raw = $this->form->getRawState();
    $this->duplicateFromSessionId = filled($raw['duplicate_from_session_id'] ?? null)
      ? (int) $raw['duplicate_from_session_id']
      : null;

    return $data;
  }

  /**
   * Recopie la configuration après création si une session modèle est choisie.
   */
  protected function afterCreate(): void
  {
    if ($this->duplicateFromSessionId === null) {
      return;
    }

    $source = AcademicSession::query()->find($this->duplicateFromSessionId);

    if ($source === null) {
      return;
    }

    try {
      $counts = app(DuplicateEcapSessionConfigurationService::class)
        ->duplicateFromSession($this->record, $source);

      $this->sendFilamentFeedback(
        Notification::make()
          ->title('Configuration reprise')
          ->success()
          ->body(
            'Depuis « '.$source->name.' » : '
            .$counts['periods'].' période(s), '
            .$counts['schedules'].' entrée(s) calendrier, '
            .$counts['staff'].' affectation(s) acteur, '
            .$counts['vacations'].' vacation(s), '
            .$counts['groups'].' groupe(s).',
          ),
      );
    } catch (\Throwable $exception) {
      report($exception);

      $this->sendFilamentFeedback(
        Notification::make()
          ->title('Duplication partielle ou échouée')
          ->warning()
          ->body($exception->getMessage()),
      );
    }
  }
}
