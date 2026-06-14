<?php

namespace App\Services\System;

use App\Enums\DeploymentOperationStatus;
use App\Models\DeploymentOperation;
use App\Models\User;
use InvalidArgumentException;

/**
 * Orchestre un déploiement production : storage, migrations, seeders et Shield.
 */
class ProductionDeployRunner
{
  /**
   * @param  SystemDeploymentService  $deploymentService  Migrations et Shield
   * @param  ProductionSeederService  $seederService  Seeders configurés
   */
  public function __construct(
    private readonly SystemDeploymentService $deploymentService,
    private readonly ProductionSeederService $seederService,
  ) {}

  /**
   * Étapes disponibles pour un déploiement HTTP.
   *
   * @var list<string>
   */
  public const STEPS = [
    'storage',
    'migrate',
    'seed',
    'shield',
  ];

  /**
   * Exécute les étapes demandées et retourne le résumé.
   *
   * @param  array<int, string>|null  $steps  Étapes à exécuter (null = toutes)
   * @param  User|null  $user  Administrateur à associer au journal (optionnel)
   * @return array{
   *   success: bool,
   *   steps: array<string, array{status: string, exit_code: int|null, operation_id: int|null, output: string|null}>
   * }
   */
  public function run(?array $steps = null, ?User $user = null): array
  {
    if (function_exists('set_time_limit')) {
      set_time_limit(300);
    }

    $steps = $this->normalizeSteps($steps);
    $summary = [
      'success' => true,
      'steps' => [],
    ];

    foreach ($steps as $step) {
      $summary['steps'][$step] = match ($step) {
        'storage' => $this->runStorageStep($user),
        'migrate' => $this->runOperationStep(
          $this->deploymentService->runMigrations($user),
        ),
        'seed' => $this->runOperationStep(
          $this->seederService->run(config('deployment.production_seeder_key'), $user),
        ),
        'shield' => $this->runOperationStep(
          $this->deploymentService->runShieldGenerate($user),
        ),
        default => throw new InvalidArgumentException('Étape inconnue : '.$step),
      };

      if ($summary['steps'][$step]['status'] !== 'success') {
        $summary['success'] = false;

        break;
      }
    }

    return $summary;
  }

  /**
   * @param  array<int, string>|null  $steps  Étapes brutes
   * @return list<string>
   */
  private function normalizeSteps(?array $steps): array
  {
    if ($steps === null || $steps === []) {
      return self::STEPS;
    }

    $allowed = array_flip(self::STEPS);
    $normalized = [];

    foreach ($steps as $step) {
      $step = strtolower(trim((string) $step));

      if ($step === '' || ! isset($allowed[$step])) {
        throw new InvalidArgumentException('Étape invalide : '.$step);
      }

      if (! in_array($step, $normalized, true)) {
        $normalized[] = $step;
      }
    }

    return $normalized;
  }

  /**
   * Prépare storage/app/public et le lien public/storage.
   *
   * @return array{status: string, exit_code: int|null, operation_id: int|null, output: string|null}
   */
  private function runStorageStep(?User $user): array
  {
    $results = $this->deploymentService->setupPublicStorage($user);
    $failed = collect($results)->contains(
      fn (DeploymentOperation $operation) => $operation->status === DeploymentOperationStatus::Failed,
    );

    $last = $results['link'] ?? $results['prepare'];

    return [
      'status' => $failed ? 'failed' : 'success',
      'exit_code' => $last->exit_code,
      'operation_id' => $last->id,
      'output' => $failed
        ? collect($results)->pluck('output')->filter()->implode(PHP_EOL)
        : 'Stockage public prêt.',
    ];
  }

  /**
   * Formate le résultat d'une opération journalisée.
   *
   * @return array{status: string, exit_code: int|null, operation_id: int|null, output: string|null}
   */
  private function runOperationStep(DeploymentOperation $operation): array
  {
    return [
      'status' => $operation->status === DeploymentOperationStatus::Success ? 'success' : 'failed',
      'exit_code' => $operation->exit_code,
      'operation_id' => $operation->id,
      'output' => $operation->output,
    ];
  }
}
