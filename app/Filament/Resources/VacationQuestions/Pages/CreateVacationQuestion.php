<?php

namespace App\Filament\Resources\VacationQuestions\Pages;

use App\Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\VacationQuestions\VacationQuestionResource;

/**
 * Création d'une question vacation ECAP.
 */
class CreateVacationQuestion extends CreateRecord
{
  protected static string $resource = VacationQuestionResource::class;
}
