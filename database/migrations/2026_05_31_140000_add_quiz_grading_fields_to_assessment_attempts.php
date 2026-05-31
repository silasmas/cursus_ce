<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Verrouillage collaboratif et traçabilité de la correction quiz.
   */
  public function up(): void
  {
    Schema::table('assessment_attempts', function (Blueprint $table) {
      $table->foreignId('grading_locked_by_user_id')
        ->nullable()
        ->after('status')
        ->constrained('users')
        ->nullOnDelete();
      $table->timestamp('grading_locked_at')->nullable()->after('grading_locked_by_user_id');
      $table->foreignId('graded_by_user_id')
        ->nullable()
        ->after('grading_locked_at')
        ->constrained('users')
        ->nullOnDelete();
      $table->timestamp('grading_notified_at')->nullable()->after('graded_by_user_id');
    });
  }

  /**
   * Supprime les colonnes de correction quiz.
   */
  public function down(): void
  {
    Schema::table('assessment_attempts', function (Blueprint $table) {
      $table->dropConstrainedForeignId('grading_locked_by_user_id');
      $table->dropConstrainedForeignId('graded_by_user_id');
      $table->dropColumn(['grading_locked_at', 'grading_notified_at']);
    });
  }
};
