<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enseignant par chapitre + validation mentor des TP.
 */
return new class extends Migration
{
  /**
   * Applique les colonnes.
   */
  public function up(): void
  {
    Schema::table('chapters', function (Blueprint $table) {
      $table->foreignId('instructor_user_id')
        ->nullable()
        ->after('course_module_id')
        ->constrained('users')
        ->nullOnDelete();
    });

    Schema::table('assignment_submissions', function (Blueprint $table) {
      $table->string('mentor_status', 20)->default('pending')->index()->after('status');
      $table->text('mentor_notes')->nullable()->after('mentor_status');
      $table->foreignId('mentor_reviewer_id')->nullable()->after('mentor_notes')->constrained('users')->nullOnDelete();
      $table->timestamp('mentor_reviewed_at')->nullable()->after('mentor_reviewer_id');
    });

    Schema::table('mentoring_decisions', function (Blueprint $table) {
      $table->foreignId('chapter_id')->nullable()->after('mentor_assignment_id')->constrained()->nullOnDelete();
      $table->foreignId('assignment_submission_id')->nullable()->after('chapter_id')->constrained()->nullOnDelete();
    });
  }

  /**
   * Annule la migration.
   */
  public function down(): void
  {
    Schema::table('mentoring_decisions', function (Blueprint $table) {
      $table->dropConstrainedForeignId('assignment_submission_id');
      $table->dropConstrainedForeignId('chapter_id');
    });

    Schema::table('assignment_submissions', function (Blueprint $table) {
      $table->dropConstrainedForeignId('mentor_reviewer_id');
      $table->dropColumn(['mentor_status', 'mentor_notes', 'mentor_reviewed_at']);
    });

    Schema::table('chapters', function (Blueprint $table) {
      $table->dropConstrainedForeignId('instructor_user_id');
    });
  }
};
