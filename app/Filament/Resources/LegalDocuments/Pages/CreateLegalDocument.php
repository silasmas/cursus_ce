<?php

namespace App\Filament\Resources\LegalDocuments\Pages;

use App\Filament\Resources\LegalDocuments\LegalDocumentResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Création d'un document légal.
 */
class CreateLegalDocument extends CreateRecord
{
  protected static string $resource = LegalDocumentResource::class;
}
