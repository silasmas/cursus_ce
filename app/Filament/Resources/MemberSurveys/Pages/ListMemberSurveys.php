<?php

namespace App\Filament\Resources\MemberSurveys\Pages;

use App\Filament\Resources\MemberSurveys\MemberSurveyResource;
use App\Filament\Resources\Pages\ListRecords;

/**
 * Liste des sondages fidèles complétés.
 */
class ListMemberSurveys extends ListRecords
{
  protected static string $resource = MemberSurveyResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }
}
