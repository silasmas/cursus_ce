<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Calendrier des modules (cours) dans une session ECAP.
   */
  public function up(): void
  {
    Schema::create('session_module_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
      $table->foreignId('course_module_id')->constrained('course_modules')->cascadeOnDelete();
      $table->date('starts_on');
      $table->date('ends_on');
      $table->unsignedSmallInteger('sort_order')->default(0);
      $table->timestamps();

      $table->unique(['academic_session_id', 'course_module_id'], 'sms_session_module_uq');
      $table->index(['academic_session_id', 'starts_on'], 'sms_session_starts_idx');
    });
  }

  /**
   * Supprime la table des plannings de modules.
   */
  public function down(): void
  {
    Schema::dropIfExists('session_module_schedules');
  }
};
