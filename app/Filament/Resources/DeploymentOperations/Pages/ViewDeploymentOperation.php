<?php

namespace App\Filament\Resources\DeploymentOperations\Pages;

use App\Filament\Resources\DeploymentOperations\DeploymentOperationResource;
use App\Filament\Resources\Pages\ViewRecord;

/**
 * Détail d'une opération de maintenance production (sortie console).
 */
class ViewDeploymentOperation extends ViewRecord
{
  protected static string $resource = DeploymentOperationResource::class;

  /**
   * Titre de la fiche détail.
   */
  public function getTitle(): string
  {
    $type = $this->getRecord()->type?->label() ?? 'Maintenance';

    return $type.' — '.$this->getRecord()->started_at?->format('d/m/Y H:i');
  }
}
