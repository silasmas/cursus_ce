<?php

namespace App\Filament\Resources\MentorTpPublications\Pages;

use App\Filament\Resources\MentorTpPublications\MentorTpPublicationResource;
use App\Filament\Resources\Pages\ListRecords;

/**
 * Liste des TP mentors à valider par l'administration.
 */
class ListMentorTpPublications extends ListRecords
{
  protected static string $resource = MentorTpPublicationResource::class;

  /**
   * Titre de la page en français.
   */
  public function getTitle(): string
  {
    return 'TP mentors à valider';
  }
}
