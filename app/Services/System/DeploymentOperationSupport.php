<?php

namespace App\Services\System;

use App\Enums\DeploymentOperationStatus;
use App\Enums\DeploymentOperationType;
use App\Models\DeploymentOperation;
use Illuminate\Support\Facades\Schema;

/**
 * Utilitaires pour journaliser ou simuler une opération de déploiement.
 */
final class DeploymentOperationSupport
{
  /**
   * Indique si la table de journal existe (post-migration).
   */
  public static function canPersist(): bool
  {
    try {
      return Schema::hasTable('deployment_operations');
    } catch (\Throwable) {
      return false;
    }
  }

  /**
   * Construit une opération en mémoire lorsque le journal n'est pas encore migré.
   *
   * @param  array<string, mixed>  $parameters  Paramètres enregistrés
   * @param  array{exit_code: int, output: string}  $result  Résultat d'exécution
   */
  public static function ephemeral(
    DeploymentOperationType $type,
    string $command,
    array $parameters,
    array $result,
  ): DeploymentOperation {
    $operation = new DeploymentOperation([
      'type' => $type,
      'command' => $command,
      'parameters' => $parameters,
      'exit_code' => (int) ($result['exit_code'] ?? 1),
      'output' => $result['output'] ?? '',
      'started_at' => now(),
      'finished_at' => now(),
    ]);

    $operation->status = ($result['exit_code'] ?? 1) === 0
      ? DeploymentOperationStatus::Success
      : DeploymentOperationStatus::Failed;

    return $operation;
  }
}
