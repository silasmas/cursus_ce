<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Ajoute la récurrence JSON pour les activités du calendrier ECAP.
   */
  public function up(): void
  {
    Schema::table('session_module_schedules', function (Blueprint $table) {
      $table->json('recurrence')->nullable()->after('description');
    });
  }

  /**
   * Supprime la colonne de récurrence.
   */
  public function down(): void
  {
    Schema::table('session_module_schedules', function (Blueprint $table) {
      $table->dropColumn('recurrence');
    });
  }
};

