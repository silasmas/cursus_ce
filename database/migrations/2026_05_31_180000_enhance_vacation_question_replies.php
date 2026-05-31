<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Types de réponses, édition et historique des révisions Q&R ECAP.
   */
  public function up(): void
  {
    Schema::table('vacation_question_replies', function (Blueprint $table) {
      if (! Schema::hasColumn('vacation_question_replies', 'reply_type')) {
        $table->string('reply_type')->default('answer')->after('body');
      }

      if (! Schema::hasColumn('vacation_question_replies', 'parent_reply_id')) {
        $table->unsignedBigInteger('parent_reply_id')->nullable()->after('body');
      }

      if (! Schema::hasColumn('vacation_question_replies', 'edited_at')) {
        $table->timestamp('edited_at')->nullable()->after('body');
      }

      if (! Schema::hasColumn('vacation_question_replies', 'version')) {
        $table->unsignedSmallInteger('version')->default(1)->after('body');
      }
    });

    if (! $this->foreignKeyExists('vacation_question_replies', 'vqr_parent_reply_fk')) {
      Schema::table('vacation_question_replies', function (Blueprint $table) {
        $table->foreign('parent_reply_id', 'vqr_parent_reply_fk')
          ->references('id')
          ->on('vacation_question_replies')
          ->nullOnDelete();
      });
    }

    if (! Schema::hasTable('vacation_reply_revisions')) {
      Schema::create('vacation_reply_revisions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('vacation_question_reply_id');
        $table->text('body');
        $table->foreignId('edited_by_user_id')->constrained('users')->cascadeOnDelete();
        $table->timestamps();

        $table->foreign('vacation_question_reply_id', 'vqr_revisions_reply_fk')
          ->references('id')
          ->on('vacation_question_replies')
          ->cascadeOnDelete();
      });
    }
  }

  /**
   * Supprime les colonnes et la table de révisions.
   */
  public function down(): void
  {
    Schema::dropIfExists('vacation_reply_revisions');

    if ($this->foreignKeyExists('vacation_question_replies', 'vqr_parent_reply_fk')) {
      Schema::table('vacation_question_replies', function (Blueprint $table) {
        $table->dropForeign('vqr_parent_reply_fk');
      });
    }

    Schema::table('vacation_question_replies', function (Blueprint $table) {
      if (Schema::hasColumn('vacation_question_replies', 'reply_type')) {
        $table->dropColumn('reply_type');
      }

      if (Schema::hasColumn('vacation_question_replies', 'parent_reply_id')) {
        $table->dropColumn('parent_reply_id');
      }

      if (Schema::hasColumn('vacation_question_replies', 'edited_at')) {
        $table->dropColumn('edited_at');
      }

      if (Schema::hasColumn('vacation_question_replies', 'version')) {
        $table->dropColumn('version');
      }
    });
  }

  /**
   * Indique si une clé étrangère existe déjà sur la table.
   */
  private function foreignKeyExists(string $table, string $name): bool
  {
    $connection = Schema::getConnection();
    $database = $connection->getDatabaseName();

    $result = $connection->select(
      'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ? LIMIT 1',
      [$database, $table, $name, 'FOREIGN KEY'],
    );

    return $result !== [];
  }
};
