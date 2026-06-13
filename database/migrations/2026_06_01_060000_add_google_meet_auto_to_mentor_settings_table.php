<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Ajoute l'option de création automatique de lien Google Meet.
   */
  public function up(): void
  {
    Schema::table('mentor_settings', function (Blueprint $table) {
      $table->boolean('google_meet_auto_create_link')->default(false)->after('zoom_auto_create_link');
    });
  }

  /**
   * Supprime la colonne.
   */
  public function down(): void
  {
    Schema::table('mentor_settings', function (Blueprint $table) {
      $table->dropColumn('google_meet_auto_create_link');
    });
  }
};
