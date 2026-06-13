<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Rôles Shield (guard `admin`) et fidèle (guard `member`) + comptes super-admin.
     */
    public function run(): void
    {
        $adminGuard = 'admin';
        $memberGuard = 'member';

        $superAdminRole = config('filament-shield.super_admin.name', 'super_admin');
        $panelUserRole = config('filament-shield.panel_user.name', 'panel_user');

        $superAdminRoleModel = Role::query()->firstOrCreate(
            ['name' => $superAdminRole, 'guard_name' => $adminGuard],
        );

        Role::query()->firstOrCreate(
            ['name' => $panelUserRole, 'guard_name' => $adminGuard],
        );

        Role::query()->firstOrCreate(
            ['name' => 'student', 'guard_name' => $memberGuard],
        );

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'silasjmas@gmail.com'],
            [
                'name' => 'Silas Mas',
                'password' => 'silasmas',
            ],
        );

        $superAdmin->syncRoles([$superAdminRoleModel]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrateur',
                'password' => 'password',
            ],
        );

        $admin->syncRoles([$superAdminRoleModel]);

        $this->call(AdminPermissionsSeeder::class);
        $this->call(FormationContentSeeder::class);
        $this->call(EcapProductionSessionSeeder::class);
        $this->call(EcapSession20CalendarSeeder::class);
        $this->call(LegalDocumentSeeder::class);
        $this->call(PortalDemoSeeder::class);
    }
}
