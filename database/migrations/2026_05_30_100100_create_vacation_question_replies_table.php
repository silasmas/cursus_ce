<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Réponses (fil type commentaires Facebook) sur une question ECAP.
   */
  public function up(): void
  {
    Schema::create('vacation_question_replies', function (Blueprint $table) {
      $table->id();
      $table->foreignId('vacation_question_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->text('body');
      $table->timestamps();

      $table->index(['vacation_question_id', 'created_at']);
    });
  }

  /**
   * Supprime la table des réponses.
   */
  public function down(): void
  {
    Schema::dropIfExists('vacation_question_replies');
  }
};
