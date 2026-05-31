<?php

namespace App\Filament\Resources\AcademicSessions\Pages;

use App\Filament\Resources\AcademicSessions\AcademicSessionResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

/**
 * Édition d'une génération ECAP (calendrier, périodes pédagogiques, vacations).
 */
class EditAcademicSession extends EditRecord
{
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
      DeleteAction::make(),
    ];
  }
}
