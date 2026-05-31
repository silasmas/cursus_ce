<?php

namespace App\Filament\Resources\MentorTpPublications\Pages;

use App\Filament\Resources\MentorTpPublications\MentorTpPublicationResource;
use App\Services\Mentor\MentorTpSubmissionService;
use Filament\Actions\Action;
use App\Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

/**
 * Détail d'une remise de TP mentor en attente ou traitée.
 */
class ViewMentorTpPublication extends ViewRecord
{
  protected static string $resource = MentorTpPublicationResource::class;

  /**
   * Titre de la page en français.
   */
  public function getTitle(): string
  {
    $assessmentTitle = $this->getRecord()->assessment?->title ?? 'TP mentor';

    return 'Détail — '.$assessmentTitle;
  }

  /**
   * Actions disponibles sur la fiche détail.
   *
   * @return array<int, Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      Action::make('openFile')
        ->label('Ouvrir le fichier')
        ->icon('heroicon-o-document-arrow-down')
        ->color('gray')
        ->url(fn (): ?string => $this->getRecord()->file_url)
        ->openUrlInNewTab()
        ->visible(fn (): bool => $this->getRecord()->hasAttachedFile()),
      Action::make('publishForMentee')
        ->label('Publier pour le mentoré')
        ->icon('heroicon-o-check-badge')
        ->color('success')
        ->visible(fn (): bool => $this->getRecord()->admin_publication_status === 'pending_review')
        ->requiresConfirmation()
        ->modalHeading('Valider et publier ce TP ?')
        ->modalDescription('Le mentoré pourra consulter cette remise dans son espace.')
        ->action(function (): void {
          app(MentorTpSubmissionService::class)->publishForMentee($this->getRecord(), Auth::user());
          $this->record->refresh();
        }),
      Action::make('reject')
        ->label('Refuser')
        ->icon('heroicon-o-x-circle')
        ->color('danger')
        ->visible(fn (): bool => $this->getRecord()->admin_publication_status === 'pending_review')
        ->requiresConfirmation()
        ->action(function (): void {
          $this->getRecord()->update(['admin_publication_status' => 'rejected']);
          $this->record->refresh();
        }),
    ];
  }
}
