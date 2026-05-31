<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Lie les questions au module de cours, aux enseignants (@ / @tous) et à l'escalade.
   */
  public function up(): void
  {
    Schema::table('vacation_questions', function (Blueprint $table) {
      $table->foreignId('course_module_id')
        ->nullable()
        ->after('session_vacation_id')
        ->constrained('course_modules')
        ->cascadeOnDelete();
      $table->foreignId('addressed_to_user_id')
        ->nullable()
        ->after('addressed_to_role')
        ->constrained('users')
        ->nullOnDelete();
      $table->boolean('is_addressed_to_all_teachers')->default(false)->after('addressed_to_user_id');
      $table->timestamp('escalation_notified_at')->nullable()->after('answered_at');
      $table->string('subject')->nullable()->change();
      $table->string('addressed_to_role', 32)->nullable()->change();
    });
  }

  /**
   * Annule les colonnes ajoutées.
   */
  public function down(): void
  {
    Schema::table('vacation_questions', function (Blueprint $table) {
      $table->dropForeign(['course_module_id']);
      $table->dropForeign(['addressed_to_user_id']);
      $table->dropColumn([
        'course_module_id',
        'addressed_to_user_id',
        'is_addressed_to_all_teachers',
        'escalation_notified_at',
      ]);
    });
  }
};
