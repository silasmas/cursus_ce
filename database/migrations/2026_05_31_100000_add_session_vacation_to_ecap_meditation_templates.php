<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portée vacation d'un cahier de méditation (session entière ou vacation ciblée).
 */
return new class extends Migration
{
  /**
   * Ajoute session_vacation_id aux modèles de cahier.
   */
  public function up(): void
  {
    Schema::table('ecap_meditation_templates', function (Blueprint $table) {
      $table->foreignId('session_vacation_id')
        ->nullable()
        ->after('academic_session_id')
        ->constrained('session_vacations')
        ->nullOnDelete();

      $table->index(['academic_session_id', 'session_vacation_id', 'is_published'], 'ecap_meditation_scope_idx');
    });
  }

  /**
   * Supprime la colonne vacation.
   */
  public function down(): void
  {
    Schema::table('ecap_meditation_templates', function (Blueprint $table) {
      $table->dropForeign(['session_vacation_id']);
      $table->dropIndex('ecap_meditation_scope_idx');
      $table->dropColumn('session_vacation_id');
    });
  }
};
