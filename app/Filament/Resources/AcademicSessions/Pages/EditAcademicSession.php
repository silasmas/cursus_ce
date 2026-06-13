<?php

namespace App\Filament\Resources\AcademicSessions\Pages;

use App\Filament\Concerns\SendsFilamentOperationFeedback;
use App\Filament\Resources\AcademicSessions\AcademicSessionResource;
use App\Filament\Resources\AcademicSessions\Schemas\DuplicateEcapSessionFormFields;
use App\Models\AcademicSession;
use App\Services\Ecap\DuplicateEcapSessionOptions;
use App\Services\Ecap\DuplicateEcapSessionService;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

/**
 * Édition d'une génération ECAP (calendrier, périodes pédagogiques, vacations).
 */
class EditAcademicSession extends EditRecord
{
  use SendsFilamentOperationFeedback;

  protected static string $resource = AcademicSessionResource::class;

  /**
   * Indique où configurer le calendrier fidèle.
   */
  public function getSubheading(): ?string
  {
    return 'Pour le calendrier visible des fidèles : ouvrez l\'onglet « Calendrier (modules & activités) » ci-dessous et renseignez les dates de chaque module.';
  }

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      Action::make('duplicateFromSession')
        ->label('Reprendre depuis une session')
        ->icon('heroicon-o-document-duplicate')
        ->modalHeading('Reprendre le contenu d\'une session précédente')
        ->modalDescription('Copie cours, quiz, TP et/ou la configuration depuis une autre génération ECAP. La configuration recopiée s\'ajoute aux éléments déjà présents. Le contenu cloné reste modifiable ensuite.')
        ->schema([
          Section::make()
            ->schema([
              DuplicateEcapSessionFormFields::sourceSessionSelect(
                'source_session_id',
                $this->getRecord(),
                dehydrated: true,
              ),
              ...DuplicateEcapSessionFormFields::duplicationOptions('source_session_id', dehydrated: true),
            ]),
        ])
        ->action(function (array $data): void {
          /** @var AcademicSession $target */
          $target = $this->getRecord();

          $sourceId = (int) ($data['source_session_id'] ?? 0);
          $source = AcademicSession::query()->find($sourceId);

          if ($source === null) {
            $this->sendFilamentFeedback(
              Notification::make()
                ->title('Session source introuvable')
                ->danger(),
            );

            return;
          }

          try {
            $service = app(DuplicateEcapSessionService::class);
            $result = $service->duplicate(
              $target,
              $source,
              DuplicateEcapSessionOptions::fromFormData($data),
            );

            $this->sendFilamentFeedback(
              Notification::make()
                ->title('Contenu repris avec succès')
                ->success()
                ->body($service->buildSummaryMessage($source, $result)),
            );
          } catch (\Throwable $exception) {
            report($exception);

            $this->sendFilamentFeedback(
              Notification::make()
                ->title('Duplication échouée')
                ->warning()
                ->body($exception->getMessage()),
            );
          }
        }),
      DeleteAction::make(),
    ];
  }
}
