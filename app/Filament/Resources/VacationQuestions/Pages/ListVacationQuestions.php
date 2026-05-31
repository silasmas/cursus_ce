<?php

namespace App\Filament\Resources\VacationQuestions\Pages;

use App\Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\VacationQuestions\VacationQuestionResource;
use Filament\Actions\CreateAction;

/**
 * Liste des questions vacation ECAP.
 */
class ListVacationQuestions extends ListRecords
{
  protected static string $resource = VacationQuestionResource::class;

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()->label('Nouvelle question'),
    ];
  }
}
