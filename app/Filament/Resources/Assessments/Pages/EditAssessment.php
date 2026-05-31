<?php

namespace App\Filament\Resources\Assessments\Pages;

use App\Filament\Support\FilamentConfig;
use App\Filament\Resources\Assessments\AssessmentResource;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Pages\EditRecord;

/**
 * Édition d'une évaluation (quiz, TP, quiz M5).
 */
class EditAssessment extends EditRecord
{
  protected static string $resource = AssessmentResource::class;

  /**
   * Description explicative sous le titre.
   */
  public function getSubheading(): ?string
  {
    if ($this->record->is_module_exit_quiz) {
      return 'Quiz de fin de module ECAP : configurez exactement 5 questions à choix multiples dans l\'onglet « Questions », chacune avec un chapitre de révision.';
    }

    return FilamentConfig::resourceAdminHelpPage(AssessmentResource::class, 'edit');
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
