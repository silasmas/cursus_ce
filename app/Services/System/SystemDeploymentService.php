<?php

namespace App\Services\System;

use App\Enums\DeploymentOperationStatus;
use App\Enums\DeploymentOperationType;
use App\Models\DeploymentOperation;
use App\Models\User;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/**
 * Exécute et diagnostique les opérations de déploiement depuis l'admin Filament.
 */
class SystemDeploymentService
{
  /**
   * @param  Migrator  $migrator  Service Laravel de migration
   */
  public function __construct(
    private readonly Migrator $migrator,
  ) {}

  /**
   * Retourne l'état de chaque fichier de migration.
   *
   * @return Collection<int, array{name: string, status: string, batch: int|null}>
   */
  public function migrationStatuses(): Collection
  {
    if (! $this->migrator->repositoryExists()) {
      return collect();
    }

    $files = $this->migrator->getMigrationFiles([database_path('migrations')]);
    $ran = collect($this->migrator->getRepository()->getRan());
    $batches = DB::table('migrations')->pluck('batch', 'migration');

    return collect($files)
      ->keys()
      ->sort()
      ->values()
      ->map(function (string $name) use ($ran, $batches): array {
        $executed = $ran->contains($name);

        return [
          'name' => $name,
          'status' => $executed ? 'executed' : 'pending',
          'batch' => $executed ? (int) ($batches[$name] ?? 0) : null,
        ];
      });
  }

  /**
   * Compte les migrations en attente d'exécution.
   */
  public function pendingMigrationCount(): int
  {
    return $this->migrationStatuses()->where('status', 'pending')->count();
  }

  /**
   * Retourne l'état du lien symbolique public/storage.
   *
   * @return array{
   *   target_exists: bool,
   *   link_exists: bool,
   *   is_symlink: bool,
   *   is_ready: bool,
   *   link_path: string,
   *   target_path: string,
   *   public_url: string
   * }
   */
  public function storageLinkStatus(): array
  {
    $linkPath = public_path('storage');
    $targetPath = storage_path('app/public');
    $targetExists = is_dir($targetPath);
    $linkExists = file_exists($linkPath);
    $isSymlink = is_link($linkPath);
    $isReady = false;

    if ($targetExists && $linkExists) {
      $linkRealPath = realpath($linkPath);
      $targetRealPath = realpath($targetPath);
      $isReady = $linkRealPath !== false
        && $targetRealPath !== false
        && $linkRealPath === $targetRealPath;
    }

    return [
      'target_exists' => $targetExists,
      'link_exists' => $linkExists,
      'is_symlink' => $isSymlink,
      'is_ready' => $isReady,
      'link_path' => $linkPath,
      'target_path' => $targetPath,
      'public_url' => asset('storage'),
    ];
  }

  /**
   * Enregistre un diagnostic sans modification (état des migrations).
   *
   * @param  User|null  $user  Administrateur demandeur
   */
  public function recordMigrationStatus(?User $user = null): DeploymentOperation
  {
    $lines = $this->migrationStatuses()->map(function (array $row): string {
      $label = $row['status'] === 'executed' ? 'OK' : 'EN ATTENTE';

      return sprintf('[%s] %s', $label, $row['name']);
    })->implode(PHP_EOL);

    $pending = $this->pendingMigrationCount();

    return $this->createOperation(
      type: DeploymentOperationType::MigrationStatus,
      command: 'migrate:status',
      parameters: [],
      user: $user,
      callback: fn (): array => [
        'exit_code' => 0,
        'output' => trim($lines.PHP_EOL.PHP_EOL.sprintf('Total en attente : %d', $pending)),
      ],
    );
  }

  /**
   * Exécute les migrations en attente (--force pour la production).
   *
   * @param  User|null  $user  Administrateur demandeur
   */
  public function runMigrations(?User $user = null): DeploymentOperation
  {
    return $this->createOperation(
      type: DeploymentOperationType::Migrate,
      command: 'migrate',
      parameters: ['--force' => true],
      user: $user,
      callback: fn (): array => $this->callArtisan('migrate', ['--force' => true]),
    );
  }

  /**
   * Génère les permissions et policies Filament Shield pour le panel admin.
   *
   * @param  User|null  $user  Administrateur demandeur
   */
  public function runShieldGenerate(?User $user = null): DeploymentOperation
  {
    return $this->createOperation(
      type: DeploymentOperationType::ShieldGenerate,
      command: 'shield:generate',
      parameters: [
        '--all' => true,
        '--panel' => 'admin',
        '--option' => 'policies_and_permissions',
      ],
      user: $user,
      callback: fn (): array => $this->callArtisan('shield:generate', [
        '--all' => true,
        '--panel' => 'admin',
        '--option' => 'policies_and_permissions',
      ]),
    );
  }

  /**
   * Crée le dossier storage/app/public s'il est absent.
   *
   * @param  User|null  $user  Administrateur demandeur
   */
  public function prepareStorageDirectory(?User $user = null): DeploymentOperation
  {
    return $this->createOperation(
      type: DeploymentOperationType::StoragePrepare,
      command: 'filesystem:ensure-storage-public',
      parameters: [],
      user: $user,
      callback: function (): array {
        $targetPath = storage_path('app/public');
        File::ensureDirectoryExists($targetPath);

        $gitignorePath = $targetPath.DIRECTORY_SEPARATOR.'.gitignore';

        if (! file_exists($gitignorePath)) {
          File::put($gitignorePath, "*\n!.gitignore\n");
        }

        return [
          'exit_code' => 0,
          'output' => 'Dossier prêt : '.$targetPath,
        ];
      },
    );
  }

  /**
   * Crée le lien symbolique public/storage (--force si déjà présent).
   *
   * @param  User|null  $user  Administrateur demandeur
   */
  public function runStorageLink(?User $user = null): DeploymentOperation
  {
    $targetPath = storage_path('app/public');
    File::ensureDirectoryExists($targetPath);

    $gitignorePath = $targetPath.DIRECTORY_SEPARATOR.'.gitignore';

    if (! file_exists($gitignorePath)) {
      File::put($gitignorePath, "*\n!.gitignore\n");
    }

    return $this->createOperation(
      type: DeploymentOperationType::StorageLink,
      command: 'storage:link',
      parameters: ['--force' => true],
      user: $user,
      callback: fn (): array => $this->callArtisan('storage:link', ['--force' => true]),
    );
  }

  /**
   * Prépare le dossier et le lien public en une seule opération.
   *
   * @param  User|null  $user  Administrateur demandeur
   * @return array{prepare: DeploymentOperation, link: DeploymentOperation}
   */
  public function setupPublicStorage(?User $user = null): array
  {
    return [
      'prepare' => $this->prepareStorageDirectory($user),
      'link' => $this->runStorageLink($user),
    ];
  }

  /**
   * Exécute une commande Artisan et retourne code de sortie + sortie console.
   *
   * @param  string  $command  Nom de la commande Artisan
   * @param  array<string, mixed>  $parameters  Options de la commande
   * @return array{exit_code: int, output: string}
   */
  private function callArtisan(string $command, array $parameters): array
  {
    $exitCode = Artisan::call($command, $parameters);
    $output = trim(Artisan::output());

    if ($output === '') {
      $output = 'Commande terminée sans sortie console.';
    }

    return [
      'exit_code' => (int) $exitCode,
      'output' => $output,
    ];
  }

  /**
   * Crée une entrée de journal, exécute le callback et enregistre le résultat.
   *
   * @param  DeploymentOperationType  $type  Type d'opération
   * @param  string  $command  Commande ou identifiant technique
   * @param  array<string, mixed>  $parameters  Paramètres enregistrés
   * @param  User|null  $user  Administrateur
   * @param  callable(): array{exit_code: int, output: string}  $callback  Logique métier
   */
  private function createOperation(
    DeploymentOperationType $type,
    string $command,
    array $parameters,
    ?User $user,
    callable $callback,
  ): DeploymentOperation {
    if (! DeploymentOperationSupport::canPersist()) {
      try {
        $result = $callback();

        return DeploymentOperationSupport::ephemeral($type, $command, $parameters, $result);
      } catch (Throwable $exception) {
        report($exception);

        return DeploymentOperationSupport::ephemeral($type, $command, $parameters, [
          'exit_code' => 1,
          'output' => trim($exception->getMessage()),
        ]);
      }
    }

    $operation = DeploymentOperation::query()->create([
      'type' => $type,
      'status' => DeploymentOperationStatus::Failed,
      'command' => $command,
      'parameters' => $parameters,
      'executed_by_user_id' => $user?->id,
      'started_at' => now(),
    ]);

    try {
      $result = $callback();

      $operation->update([
        'status' => ($result['exit_code'] ?? 1) === 0
          ? DeploymentOperationStatus::Success
          : DeploymentOperationStatus::Failed,
        'output' => Str::limit($result['output'] ?? '', 65000, '…'),
        'exit_code' => $result['exit_code'] ?? 1,
        'finished_at' => now(),
      ]);
    } catch (Throwable $exception) {
      report($exception);

      $operation->update([
        'status' => DeploymentOperationStatus::Failed,
        'output' => trim(($exception->getMessage()).PHP_EOL.PHP_EOL.$exception->getTraceAsString()),
        'exit_code' => 1,
        'finished_at' => now(),
      ]);
    }

    return $operation->fresh(['executedBy']);
  }
}
