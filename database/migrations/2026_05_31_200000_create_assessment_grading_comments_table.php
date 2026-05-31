<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Avis des acteurs ECAP sur une correction de quiz déjà publiée.
   */
  public function up(): void
  {
    Schema::create('assessment_grading_comments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->text('body');
      $table->timestamps();
    });
  }

  /**
   * Supprime la table des avis de correction quiz.
   */
  public function down(): void
  {
    Schema::dropIfExists('assessment_grading_comments');
  }
};
