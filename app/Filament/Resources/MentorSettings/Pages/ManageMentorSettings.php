<?php

namespace App\Filament\Resources\MentorSettings\Pages;

use App\Filament\Resources\MentorSettings\MentorSettingResource;
use App\Services\Mentor\MentorSettingService;
use Filament\Resources\Pages\EditRecord;

/**
 * Page unique de gestion des paramètres mentorat (singleton).
 */
class ManageMentorSettings extends EditRecord
{
  protected static string $resource = MentorSettingResource::class;

  protected static ?string $title = 'Paramètres mentorat';

  /**
   * Charge ou crée l'unique enregistrement de configuration.
   */
  public function mount(int|string|null $record = null): void
  {
    $this->record = app(MentorSettingService::class)->current();

    $this->authorizeAccess();

    $this->fillForm();

    $this->previousUrl = url()->previous();
  }

  /**
   * Pas de suppression sur la configuration globale.
   *
   * @return array<int, mixed>
   */
  protected function getHeaderActions(): array
  {
    return [];
  }

  /**
   * Redirige vers la même page après enregistrement.
   */
  protected function getRedirectUrl(): ?string
  {
    return static::getResource()::getUrl('index');
  }
}
