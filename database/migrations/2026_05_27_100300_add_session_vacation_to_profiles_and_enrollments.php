<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Lie le profil / l'inscription à une vacation choisie (présentiel ECAP).
   */
  public function up(): void
  {
    Schema::table('profiles', function (Blueprint $table) {
      $table->foreignId('session_vacation_id')
        ->nullable()
        ->after('vacation_choice')
        ->constrained('session_vacations')
        ->nullOnDelete();
    });

    Schema::table('enrollments', function (Blueprint $table) {
      $table->foreignId('session_vacation_id')
        ->nullable()
        ->after('is_online')
        ->constrained('session_vacations')
        ->nullOnDelete();
    });
  }

  /**
   * Supprime les clés étrangères vacation.
   */
  public function down(): void
  {
    Schema::table('profiles', function (Blueprint $table) {
      $table->dropConstrainedForeignId('session_vacation_id');
    });

    Schema::table('enrollments', function (Blueprint $table) {
      $table->dropConstrainedForeignId('session_vacation_id');
    });
  }
};
