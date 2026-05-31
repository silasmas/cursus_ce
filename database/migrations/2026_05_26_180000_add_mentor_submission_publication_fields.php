<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet au mentor de remettre un TP pour un mentoré, visible après validation admin.
 */
return new class extends Migration
{
  /**
   * Ajoute les colonnes de publication et auteur de la remise.
   */
  public function up(): void
  {
    Schema::table('assignment_submissions', function (Blueprint $table) {
      $table->foreignId('submitted_by_user_id')
        ->nullable()
        ->after('user_id')
        ->constrained('users')
        ->nullOnDelete();

      $table->boolean('visible_to_mentee')
        ->default(true)
        ->after('submitted_at');

      $table->string('admin_publication_status', 30)
        ->default('published')
        ->index()
        ->after('visible_to_mentee');
    });
  }

  /**
   * Supprime les colonnes ajoutées.
   */
  public function down(): void
  {
    Schema::table('assignment_submissions', function (Blueprint $table) {
      $table->dropConstrainedForeignId('submitted_by_user_id');
      $table->dropColumn(['visible_to_mentee', 'admin_publication_status']);
    });
  }
};
