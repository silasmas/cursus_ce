<?php

namespace App\Filament\Resources\EcapStaffAssignments\Pages;

use App\Filament\Resources\EcapStaffAssignments\EcapStaffAssignmentResource;
use App\Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

/**
 * Liste des acteurs de vacation ECAP.
 */
class ListEcapStaffAssignments extends ListRecords
{
  protected static string $resource = EcapStaffAssignmentResource::class;

  /**
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()->label('Affecter un acteur'),
    ];
  }
}
