<?php

namespace App\Services\System;

use App\Enums\DeploymentOperationStatus;
use App\Models\User;
use Database\Seeders\AdminPermissionsSeeder;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Throwable;

/**
 * Initialise le compte super administrateur et les permissions Shield en production.
 */
class SuperAdminBootstrapService
{
  /**
   * @param  SystemDeploymentService  $deploymentService  Génération Shield
   */
  public function __construct(
    private readonly SystemDeploymentService $deploymentService,
  ) {}

  /**
   * Crée le super admin, génère Shield et synchronise les permissions.
   *
   * @return array{
   *   success: bool,
   *   admin: array{email: string, name: string, role: string}|null,
   *   steps: array<string, array{status: string, output: string|null}>
   * }
   */
  public function run(): array
  {
    $this->ensureDatabaseIsReady();

    $summary = [
      'success' => true,
      'admin' => null,
      'steps' => [],
    ];

    $pipeline = [
      'roles' => fn (): array => $this->ensureAdminRoles(),
      'shield' => fn (): array => $this->generateShieldPermissions(),
      'permissions' => fn (): array => $this->syncAdminPermissions(),
      'user' => fn (): array => $this->createSuperAdminUser(),
    ];

    foreach ($pipeline as $step => $handler) {
      $summary['steps'][$step] = $handler();

      if ($summary['steps'][$step]['status'] !== 'success') {
        $summary['success'] = false;

        break;
      }
    }

    if ($summary['success']) {
      $email = (string) config('deployment.bootstrap_admin.email', 'admin@ce.church');

      $summary['admin'] = [
        'email' => $email,
        'name' => (string) config('deployment.bootstrap_admin.name', 'Administrateur CE'),
        'role' => (string) config('filament-shield.super_admin.name', 'super_admin'),
      ];
    }

    return $summary;
  }

  /**
   * Vérifie que les tables nécessaires existent avant l'initialisation.
   */
  private function ensureDatabaseIsReady(): void
  {
    $requiredTables = ['users', 'roles', 'permissions', 'model_has_roles'];

    foreach ($requiredTables as $table) {
      if (! Schema::hasTable($table)) {
        throw new InvalidArgumentException(
          'Table « '.$table.' » absente. Exécutez d\'abord les migrations via run-production-deploy.php.',
        );
      }
    }
  }

  /**
   * Crée les rôles Shield admin s'ils n'existent pas encore.
   *
   * @return array{status: string, output: string|null}
   */
  private function ensureAdminRoles(): array
  {
    try {
      $adminGuard = 'admin';
      $superAdminRole = (string) config('filament-shield.super_admin.name', 'super_admin');
      $panelUserRole = (string) config('filament-shield.panel_user.name', 'panel_user');

      Role::query()->firstOrCreate([
        'name' => $superAdminRole,
        'guard_name' => $adminGuard,
      ]);

      Role::query()->firstOrCreate([
        'name' => $panelUserRole,
        'guard_name' => $adminGuard,
      ]);

      return [
        'status' => 'success',
        'output' => 'Rôles '.$superAdminRole.' et '.$panelUserRole.' prêts (guard admin).',
      ];
    } catch (Throwable $exception) {
      report($exception);

      return [
        'status' => 'failed',
        'output' => $exception->getMessage(),
      ];
    }
  }

  /**
   * Génère les permissions et policies Filament Shield pour le panel admin.
   *
   * @return array{status: string, output: string|null}
   */
  private function generateShieldPermissions(): array
  {
    try {
      $operation = $this->deploymentService->runShieldGenerate();

      return [
        'status' => $operation->status === DeploymentOperationStatus::Success ? 'success' : 'failed',
        'output' => $operation->output,
      ];
    } catch (Throwable $exception) {
      report($exception);

      return [
        'status' => 'failed',
        'output' => $exception->getMessage(),
      ];
    }
  }

  /**
   * Attribue toutes les permissions au rôle super_admin.
   *
   * @return array{status: string, output: string|null}
   */
  private function syncAdminPermissions(): array
  {
    try {
      app(AdminPermissionsSeeder::class)->run();

      return [
        'status' => 'success',
        'output' => 'Permissions synchronisées sur super_admin et panel_user.',
      ];
    } catch (Throwable $exception) {
      report($exception);

      return [
        'status' => 'failed',
        'output' => $exception->getMessage(),
      ];
    }
  }

  /**
   * Crée ou met à jour le compte super administrateur configuré.
   *
   * @return array{status: string, output: string|null}
   */
  private function createSuperAdminUser(): array
  {
    try {
      $email = (string) config('deployment.bootstrap_admin.email', 'admin@ce.church');
      $password = (string) config('deployment.bootstrap_admin.password', 'silasmas');
      $name = (string) config('deployment.bootstrap_admin.name', 'Administrateur CE');
      $superAdminRole = (string) config('filament-shield.super_admin.name', 'super_admin');

      $user = User::query()->updateOrCreate(
        ['email' => $email],
        [
          'name' => $name,
          'password' => $password,
        ],
      );

      $role = Role::query()->firstWhere([
        'name' => $superAdminRole,
        'guard_name' => 'admin',
      ]);

      if ($role === null) {
        throw new InvalidArgumentException('Rôle super_admin introuvable après initialisation.');
      }

      $user->syncRoles([$role]);

      return [
        'status' => 'success',
        'output' => 'Compte super admin prêt : '.$email.' (mot de passe mis à jour).',
      ];
    } catch (Throwable $exception) {
      report($exception);

      return [
        'status' => 'failed',
        'output' => $exception->getMessage(),
      ];
    }
  }
}
