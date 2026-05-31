<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Liste des documents légaux.
 */
class ListLegalDocuments extends ListRecords
{
  protected static string $resource = LegalDocumentResource::class;

  /**
   * Actions de la page liste.
   *
   * @return array<int, mixed>
   */
  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make(),
    ];
  }
}
