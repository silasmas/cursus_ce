<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Questions des fidèles adressées aux acteurs de vacation ECAP.
   */
  public function up(): void
  {
    Schema::create('vacation_questions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
      $table->foreignId('session_vacation_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('asked_by_user_id')->constrained('users')->cascadeOnDelete();
      $table->string('addressed_to_role', 32);
      $table->string('subject');
      $table->text('body');
      $table->string('status', 32)->default('pending');
      $table->foreignId('answered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->text('answer_body')->nullable();
      $table->timestamp('answered_at')->nullable();
      $table->timestamps();

      $table->index(['academic_session_id', 'status']);
      $table->index(['addressed_to_role', 'status']);
    });
  }

  /**
   * Supprime la table des questions vacation.
   */
  public function down(): void
  {
    Schema::dropIfExists('vacation_questions');
  }
};
