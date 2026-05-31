<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Mode de suivi ECAP : true = en ligne, false = présentiel.
   */
  public function up(): void
  {
    Schema::table('enrollments', function (Blueprint $table) {
      $table->boolean('is_online')->default(true)->after('academic_session_id');
      $table->timestamp('online_mode_updated_at')->nullable()->after('is_online');
      $table->foreignId('online_mode_updated_by_user_id')
        ->nullable()
        ->after('online_mode_updated_at')
        ->constrained('users')
        ->nullOnDelete();
    });
  }

  /**
   * Supprime les colonnes de mode en ligne ECAP.
   */
  public function down(): void
  {
    Schema::table('enrollments', function (Blueprint $table) {
      $table->dropConstrainedForeignId('online_mode_updated_by_user_id');
      $table->dropColumn(['is_online', 'online_mode_updated_at']);
    });
  }
};
