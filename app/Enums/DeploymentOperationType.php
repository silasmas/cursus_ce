<?php

namespace App\Enums;

/**
 * Types d'opérations de maintenance exécutables depuis l'admin Filament.
 */
enum DeploymentOperationType: string
{
  case Migrate = 'migrate';
  case MigrationStatus = 'migration_status';
  case ShieldGenerate = 'shield_generate';
  case StoragePrepare = 'storage_prepare';
  case StorageLink = 'storage_link';
  case SeederRun = 'seeder_run';

  /**
   * Libellé français affiché dans l'interface admin.
   */
  public function label(): string
  {
    return match ($this) {
      self::Migrate => 'Migrations',
      self::MigrationStatus => 'État des migrations',
      self::ShieldGenerate => 'Permissions Shield',
      self::StoragePrepare => 'Dossier storage/app/public',
      self::StorageLink => 'Lien public/storage',
      self::SeederRun => 'Seeder production',
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
