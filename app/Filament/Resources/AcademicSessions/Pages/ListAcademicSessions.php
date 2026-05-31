<?php

namespace App\Filament\Resources\AcademicSessions\Pages;

use App\Filament\Resources\AcademicSessions\AcademicSessionResource;
use Filament\Actions\CreateAction;
use App\Filament\Resources\Pages\ListRecords;

/**
 * Liste des générations ECAP.
 */
class ListAcademicSessions extends ListRecords
{
  protected static string $resource = AcademicSessionResource::class;

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()->label('Nouvelle session ECAP'),
    ];
  }
}
