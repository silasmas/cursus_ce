<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Quiz de fin de module ECAP (M5) : lien module + chapitre de révision par question.
   */
  public function up(): void
  {
    Schema::table('assessments', function (Blueprint $table) {
      $table->foreignId('course_module_id')->nullable()->after('chapter_id')
        ->constrained('course_modules')->nullOnDelete();
      $table->boolean('is_module_exit_quiz')->default(false)->after('type');
    });

    Schema::table('questions', function (Blueprint $table) {
      $table->foreignId('review_chapter_id')->nullable()->after('points')
        ->constrained('chapters')->nullOnDelete();
    });
  }

  /**
   * Supprime les champs M5.
   */
  public function down(): void
  {
    Schema::table('questions', function (Blueprint $table) {
      $table->dropForeign(['review_chapter_id']);
      $table->dropColumn('review_chapter_id');
    });

    Schema::table('assessments', function (Blueprint $table) {
      $table->dropForeign(['course_module_id']);
      $table->dropColumn(['course_module_id', 'is_module_exit_quiz']);
    });
  }
};
