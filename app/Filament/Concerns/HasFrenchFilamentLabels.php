<?php

namespace App\Filament\Concerns;

use Filament\Navigation\NavigationItem;

/**
 * Étiquettes françaises et aide contextuelle pour les ressources Filament.
 */
trait HasFrenchFilamentLabels
{
  /**
   * Retourne les libellés FR définis dans config/filament_labels.php.
   *
   * @return array{singular?: string, plural?: string, navigation?: string}
   */
  protected static function frenchLabelConfig(): array
  {
    $key = str_replace('\\', '.', static::class);

    return config("filament_labels.{$key}", []);
  }

  /**
   * Retourne l'aide admin (info-bulle menu, descriptions de pages).
   *
   * @return array<string, mixed>
   */
  protected static function adminHelpConfig(): array
  {
    $key = str_replace('\\', '.', static::class);

    return config("filament_admin_help.resources.{$key}", []);
  }

  /**
   * Libellé singulier du modèle.
   */
  public static function getModelLabel(): string
  {
    return static::frenchLabelConfig()['singular'] ?? parent::getModelLabel();
  }

  /**
   * Libellé pluriel du modèle.
   */
  public static function getPluralModelLabel(): string
  {
    return static::frenchLabelConfig()['plural'] ?? parent::getPluralModelLabel();
  }

  /**
   * Libellé du menu de navigation.
   */
  public static function getNavigationLabel(): string
  {
    $config = static::frenchLabelConfig();

    return $config['navigation'] ?? $config['plural'] ?? parent::getNavigationLabel();
  }

  /**
   * Entrées de navigation avec info-bulle d'aide (icône ℹ en fin de ligne).
   *
   * @return array<NavigationItem>
   */
  public static function getNavigationItems(): array
  {
    $items = parent::getNavigationItems();
    $tooltip = static::adminHelpConfig()['navigation_tooltip']
      ?? 'Ouvrez « '.static::getNavigationLabel().' » pour consulter, créer ou modifier les enregistrements de cette rubrique. Survolez l’icône ℹ des autres menus pour une aide détaillée.';

    if (isset($items[0])) {
      $items[0] = $items[0]->extraAttributes([
        'data-navigation-help' => $tooltip,
      ]);
    }

    return $items;
  }
}
