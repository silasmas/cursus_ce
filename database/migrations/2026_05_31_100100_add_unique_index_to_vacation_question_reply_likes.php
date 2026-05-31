<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Ajoute l'index unique manquant (nom court pour MySQL).
   */
  public function up(): void
  {
    Schema::table('vacation_question_reply_likes', function (Blueprint $table) {
      if (! $this->indexExists()) {
        $table->unique(['vacation_question_reply_id', 'user_id'], 'vq_reply_likes_unique');
      }
    });
  }

  /**
   * Supprime l'index unique.
   */
  public function down(): void
  {
    Schema::table('vacation_question_reply_likes', function (Blueprint $table) {
      $table->dropUnique('vq_reply_likes_unique');
    });
  }

  /**
   * Vérifie si l'index existe déjà.
   */
  private function indexExists(): bool
  {
    $connection = Schema::getConnection();
    $indexes = $connection->select(
      'SHOW INDEX FROM vacation_question_reply_likes WHERE Key_name = ?',
      ['vq_reply_likes_unique'],
    );

    return count($indexes) > 0;
  }
};
