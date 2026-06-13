<?php

namespace App\Filament\Resources\AcademicSessions\Pages;

use App\Filament\Concerns\SendsFilamentOperationFeedback;
use App\Filament\Resources\AcademicSessions\AcademicSessionResource;
use App\Models\AcademicSession;
use App\Services\Ecap\DuplicateEcapSessionOptions;
use App\Services\Ecap\DuplicateEcapSessionService;
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
   * Options de duplication choisies dans le formulaire.
   */
  private ?DuplicateEcapSessionOptions $duplicateOptions = null;

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
    $this->duplicateOptions = DuplicateEcapSessionOptions::fromFormData($raw);

    return $data;
  }

  /**
   * Recopie le contenu pédagogique et la configuration après création si une session modèle est choisie.
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
      $service = app(DuplicateEcapSessionService::class);
      $result = $service->duplicate(
        $this->record,
        $source,
        $this->duplicateOptions ?? new DuplicateEcapSessionOptions(),
      );

      $this->sendFilamentFeedback(
        Notification::make()
          ->title('Session reprise avec succès')
          ->success()
          ->body($service->buildSummaryMessage($source, $result)),
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
