<?php

namespace App\Filament\Resources\DeploymentOperations\Pages;

use App\Filament\Resources\DeploymentOperations\DeploymentOperationResource;
use App\Filament\Resources\DeploymentOperations\Widgets\DeploymentMaintenancePanelWidget;
use App\Filament\Resources\Pages\ListRecords;

/**
 * Page de maintenance production : diagnostic, exécution et journal.
 */
class ListDeploymentOperations extends ListRecords
{
  protected static string $resource = DeploymentOperationResource::class;

  /**
   * Widget unique : description, actions, badges et migrations pliables.
   *
   * @return array<int, class-string>
   */
  protected function getHeaderWidgets(): array
  {
    return [
      DeploymentMaintenancePanelWidget::class,
    ];
  }

  /**
   * Les actions sont gérées dans le panneau de maintenance (pleine largeur).
   *
   * @return array<int, \Filament\Actions\Action>
   */
  protected function getHeaderActions(): array
  {
    return [];
  }
}
