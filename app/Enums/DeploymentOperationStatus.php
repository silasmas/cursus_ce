<?php

namespace App\Enums;

/**
 * Statut d'une opération de déploiement enregistrée.
 */
enum DeploymentOperationStatus: string
{
  case Success = 'success';
  case Failed = 'failed';

  /**
   * Libellé français du statut.
   */
  public function label(): string
  {
    return match ($this) {
      self::Success => 'Réussi',
      self::Failed => 'Échoué',
    };
  }

  /**
   * Couleur Filament associée au statut.
   */
  public function color(): string
  {
    return match ($this) {
      self::Success => 'success',
      self::Failed => 'danger',
    };
  }

  /**
   * Options pour les filtres Filament.
   *
   * @return array<string, string>
   */
  public static function options(): array
  {
    $options = [];

    foreach (self::cases() as $case) {
      $options[$case->value] = $case->label();
    }

    return $options;
  }
}
