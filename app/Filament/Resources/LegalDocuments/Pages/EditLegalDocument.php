<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Édition d'un document légal.
 */
class EditLegalDocument extends EditRecord
{
  protected static string $resource = LegalDocumentResource::class;

  /**
   * Actions d'en-tête.
   *
   * @return array<int, mixed>
   */
  protected function getHeaderActions(): array
  {
    return [
      DeleteAction::make(),
    ];
  }
}
