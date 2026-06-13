<?php

namespace App\Services\System;

use App\Enums\DeploymentOperationStatus;
use App\Enums\DeploymentOperationType;
use App\Models\DeploymentOperation;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * Exécute les seeders de démarrage production depuis l'admin Filament.
 */
class ProductionSeederService
{
  /**
   * Retourne le catalogue des seeders configurés, regroupés.
   *
   * @return array<string, array{label: string, items: array<int, array<string, mixed>>}>
   */
  public function catalog(): array
  {
    $groups = config('production_seeders.groups', []);
    $seeders = config('production_seeders.seeders', []);
    $catalog = [];

    foreach ($groups as $groupKey => $groupLabel) {
      $catalog[$groupKey] = [
        'label' => $groupLabel,
        'items' => [],
      ];
    }

    foreach ($seeders as $key => $definition) {
      $group = $definition['group'] ?? 'production';

      if (! isset($catalog[$group])) {
        $catalog[$group] = [
          'label' => $group,
          'items' => [],
        ];
      }

      $catalog[$group]['items'][] = array_merge($definition, [
        'key' => $key,
        'class' => $definition['class'] ?? null,
      ]);
    }

    return $catalog;
  }

  /**
   * Liste plate des seeders disponibles.
   *
   * @return array<string, array<string, mixed>>
   */
  public function all(): array
  {
    return config('production_seeders.seeders', []);
  }

  /**
   * Exécute un seeder configuré et journalise l'opération.
   *
   * @param  string  $seederKey  Clé du seeder (config production_seeders)
   * @param  User|null  $user  Administrateur demandeur
   */
  public function run(string $seederKey, ?User $user = null): DeploymentOperation
  {
    $definition = $this->all()[$seederKey] ?? null;
    $class = is_array($definition) ? ($definition['class'] ?? null) : null;

    if (! is_string($class) || ! class_exists($class)) {
      throw new InvalidArgumentException('Seeder inconnu ou classe invalide : '.$seederKey);
    }

    $operation = DeploymentOperation::query()->create([
      'type' => DeploymentOperationType::SeederRun,
      'status' => DeploymentOperationStatus::Failed,
      'command' => 'db:seed',
      'parameters' => [
        'seeder_key' => $seederKey,
        'class' => $class,
        'label' => $definition['label'] ?? $seederKey,
      ],
      'executed_by_user_id' => $user?->id,
      'started_at' => now(),
    ]);

    try {
      $exitCode = Artisan::call('db:seed', [
        '--class' => $class,
        '--force' => true,
      ]);

      $output = trim(Artisan::output());

      if ($output === '') {
        $output = 'Seeder « '.($definition['label'] ?? $seederKey).' » terminé.';
      }

      $operation->update([
        'status' => $exitCode === 0
          ? DeploymentOperationStatus::Success
          : DeploymentOperationStatus::Failed,
        'output' => Str::limit($output, 65000, '…'),
        'exit_code' => (int) $exitCode,
        'finished_at' => now(),
      ]);
    } catch (Throwable $exception) {
      report($exception);

      $operation->update([
        'status' => DeploymentOperationStatus::Failed,
        'output' => trim($exception->getMessage()),
        'exit_code' => 1,
        'finished_at' => now(),
      ]);
    }

    return $operation->fresh(['executedBy']);
  }

  /**
   * Message de confirmation pour un seeder.
   */
  public function confirmMessage(string $seederKey): string
  {
    $definition = $this->all()[$seederKey] ?? [];

    return (string) Arr::get($definition, 'confirm', 'Exécuter ce seeder ?');
  }
}
