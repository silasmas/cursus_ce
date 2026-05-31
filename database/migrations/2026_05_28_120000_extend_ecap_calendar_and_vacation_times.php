<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Étend le calendrier ECAP : activités, périodes, horaires vacations.
   */
  public function up(): void
  {
    Schema::table('session_module_schedules', function (Blueprint $table) {
      $table->string('item_type', 32)->default('module')->after('academic_session_id');
      $table->foreignId('session_period_id')->nullable()->after('course_module_id')
        ->constrained('session_periods')->nullOnDelete();
      $table->string('title')->nullable()->after('session_period_id');
      $table->text('description')->nullable()->after('title');
    });

    Schema::table('session_module_schedules', function (Blueprint $table) {
      $table->dropForeign(['course_module_id']);
    });

    Schema::table('session_module_schedules', function (Blueprint $table) {
      $table->foreignId('course_module_id')->nullable()->change();
      $table->foreign('course_module_id')->references('id')->on('course_modules')->cascadeOnDelete();
    });

    Schema::table('session_vacations', function (Blueprint $table) {
      $table->time('time_starts')->nullable()->after('code');
      $table->time('time_ends')->nullable()->after('time_starts');
    });
  }

  /**
   * Annule les extensions calendrier et vacations.
   */
  public function down(): void
  {
    Schema::table('session_vacations', function (Blueprint $table) {
      $table->dropColumn(['time_starts', 'time_ends']);
    });

    Schema::table('session_module_schedules', function (Blueprint $table) {
      $table->dropForeign(['session_period_id']);
      $table->dropColumn(['item_type', 'session_period_id', 'title', 'description']);
    });
  }
};
