<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Synchronise les permissions Shield (guard admin) sur les rôles administrateurs.
 */
class AdminPermissionsSeeder extends Seeder
{
  /**
   * Attribue toutes les permissions admin au super_admin et les droits de lecture au panel_user.
   */
  public function run(): void
  {
    $adminGuard = 'admin';
    $permissions = Permission::query()
      ->where('guard_name', $adminGuard)
      ->get();

    $superAdminRole = Role::query()->firstWhere([
      'name' => config('filament-shield.super_admin.name', 'super_admin'),
      'guard_name' => $adminGuard,
    ]);

    if ($superAdminRole !== null) {
      $superAdminRole->syncPermissions($permissions);
    }

    $panelUserRole = Role::query()->firstWhere([
      'name' => config('filament-shield.panel_user.name', 'panel_user'),
      'guard_name' => $adminGuard,
    ]);

    if ($panelUserRole !== null) {
      $panelUserRole->syncPermissions(
        $permissions->filter(fn (Permission $permission): bool => str_starts_with($permission->name, 'View'))
      );
    }
  }
}
