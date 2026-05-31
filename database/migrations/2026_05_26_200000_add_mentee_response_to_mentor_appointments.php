<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réponse du mentoré aux rendez-vous (accepter, refuser, reporter).
 */
return new class extends Migration
{
  /**
   * Ajoute les colonnes de réponse mentoré.
   */
  public function up(): void
  {
    Schema::table('mentor_appointments', function (Blueprint $table) {
      $table->string('mentee_response', 20)->default('pending')->after('status');
      $table->timestamp('proposed_reschedule_at')->nullable()->after('mentee_response');
      $table->text('response_note')->nullable()->after('proposed_reschedule_at');
      $table->timestamp('responded_at')->nullable()->after('response_note');
    });
  }

  /**
   * Supprime les colonnes ajoutées.
   */
  public function down(): void
  {
    Schema::table('mentor_appointments', function (Blueprint $table) {
      $table->dropColumn([
        'mentee_response',
        'proposed_reschedule_at',
        'response_note',
        'responded_at',
      ]);
    });
  }
};
