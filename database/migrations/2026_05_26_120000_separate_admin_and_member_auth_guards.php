<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Répartit les rôles Spatie sur les guards `admin` (Filament) et `member` (portail fidèle).
 */
return new class extends Migration
{
    /**
     * Met à jour les guards des rôles et permissions existants.
     */
    public function up(): void
    {
        if (! $this->tableExists('roles')) {
            return;
        }

        DB::table('roles')
            ->whereIn('name', ['super_admin', 'panel_user'])
            ->where('guard_name', 'web')
            ->update(['guard_name' => 'admin']);

        DB::table('roles')
            ->where('name', 'student')
            ->where('guard_name', 'web')
            ->update(['guard_name' => 'member']);

        if ($this->tableExists('permissions')) {
            DB::table('permissions')
                ->where('guard_name', 'web')
                ->update(['guard_name' => 'admin']);
        }
    }

    /**
     * Restaure le guard `web` unique (rollback).
     */
    public function down(): void
    {
        if (! $this->tableExists('roles')) {
            return;
        }

        DB::table('roles')
            ->whereIn('name', ['super_admin', 'panel_user', 'student'])
            ->whereIn('guard_name', ['admin', 'member'])
            ->update(['guard_name' => 'web']);

        if ($this->tableExists('permissions')) {
            DB::table('permissions')
                ->where('guard_name', 'admin')
                ->update(['guard_name' => 'web']);
        }
    }

    /**
     * Vérifie la présence d'une table avant mise à jour.
     *
     * @param  string  $table  Nom de la table
     */
    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
};
