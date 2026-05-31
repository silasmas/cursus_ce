<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Étend les sessions académiques (générations ECAP) pour les inscriptions publiques.
   */
  public function up(): void
  {
    Schema::table('academic_sessions', function (Blueprint $table) {
      $table->unsignedSmallInteger('generation_number')->nullable()->after('code');
      $table->timestamp('registration_opens_at')->nullable()->after('ends_on');
      $table->timestamp('registration_closes_at')->nullable()->after('registration_opens_at');
    });
  }

  /**
   * Supprime les colonnes d'inscription publique.
   */
  public function down(): void
  {
    Schema::table('academic_sessions', function (Blueprint $table) {
      $table->dropColumn([
        'generation_number',
        'registration_opens_at',
        'registration_closes_at',
      ]);
    });
  }
};
