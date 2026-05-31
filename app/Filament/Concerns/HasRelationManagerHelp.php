<?php

namespace App\Filament\Concerns;

/**
 * Affiche une description explicative sous le titre d'un RelationManager Filament.
 */
trait HasRelationManagerHelp
{
  /**
   * Clé config/ecap_admin_help.php → relation_managers.
   */
  abstract protected static function helpKey(): string;

  /**
   * Texte d'aide affiché au-dessus du contenu de l'onglet.
   */
  public static function getDescription(): ?string
  {
    return config('ecap_admin_help.relation_managers.'.static::helpKey());
  }
}
