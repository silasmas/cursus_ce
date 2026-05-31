<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Pouce « j'aime » sur une réponse du fil Q&R ECAP.
   */
  public function up(): void
  {
    if (Schema::hasTable('vacation_question_reply_likes')) {
      return;
    }

    Schema::create('vacation_question_reply_likes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('vacation_question_reply_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->timestamps();

      $table->unique(['vacation_question_reply_id', 'user_id'], 'vq_reply_likes_unique');
    });
  }

  /**
   * Supprime la table des likes.
   */
  public function down(): void
  {
    Schema::dropIfExists('vacation_question_reply_likes');
  }
};
