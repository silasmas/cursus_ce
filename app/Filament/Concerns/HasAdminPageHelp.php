<?php

namespace App\Filament\Concerns;

use App\Filament\Resources\Pages\CreateRecord as AppCreateRecord;
use App\Filament\Resources\Pages\EditRecord as AppEditRecord;
use App\Filament\Resources\Pages\ViewRecord as AppViewRecord;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

/**
 * Description de page admin lue depuis config/filament_admin_help.php.
 */
trait HasAdminPageHelp
{
  /**
   * Sous-titre explicatif affiché sous le titre de la page.
   */
  public function getSubheading(): ?string
  {
    $resourceClass = static::$resource ?? null;

    if ($resourceClass === null) {
      return null;
    }

    $resourceKey = str_replace('\\', '.', $resourceClass);
    $pageType = $this->adminPageType();

    $configured = config("filament_admin_help.resources.{$resourceKey}.pages.{$pageType}")
      ?? config("filament_admin_help.resources.{$resourceKey}.pages.list");

    if (filled($configured)) {
      return $configured;
    }

    return $this->defaultPageDescription($resourceClass, $pageType);
  }

  /**
   * Type de page Filament (liste, création, édition, consultation).
   */
  protected function adminPageType(): string
  {
    if ($this instanceof AppCreateRecord || $this instanceof CreateRecord) {
      return 'create';
    }

    if ($this instanceof AppEditRecord || $this instanceof EditRecord) {
      return 'edit';
    }

    if ($this instanceof AppViewRecord || $this instanceof ViewRecord) {
      return 'view';
    }

    return 'list';
  }

  /**
   * Description par défaut si aucun texte n'est configuré.
   */
  protected function defaultPageDescription(string $resourceClass, string $pageType): string
  {
    $plural = $resourceClass::getPluralModelLabel();
    $singular = $resourceClass::getModelLabel();

    return match ($pageType) {
      'create' => "Créez un nouvel enregistrement : {$singular}.",
      'edit' => "Modifiez les informations de ce {$singular}.",
      'view' => "Consultez le détail de ce {$singular}.",
      default => "Consultez et gérez la liste des {$plural}.",
    };
  }
}
