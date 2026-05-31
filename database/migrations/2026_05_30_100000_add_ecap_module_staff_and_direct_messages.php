<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Module ECAP par acteur et messages privés fidèle ↔ acteurs.
   */
  public function up(): void
  {
    if (! Schema::hasColumn('ecap_staff_assignments', 'course_module_id')) {
      Schema::table('ecap_staff_assignments', function (Blueprint $table) {
        $table->foreignId('course_module_id')
          ->nullable()
          ->after('session_vacation_id')
          ->constrained()
          ->nullOnDelete();
      });
    }

    if (! Schema::hasTable('ecap_direct_messages')) {
      Schema::create('ecap_direct_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
        $table->string('subject_context', 32)->default('general');
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->text('body');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();

        $table->index(['academic_session_id', 'sender_user_id', 'recipient_user_id'], 'ecap_dm_session_users_idx');
      });
    } elseif (! $this->indexExists('ecap_direct_messages', 'ecap_dm_session_users_idx')) {
      Schema::table('ecap_direct_messages', function (Blueprint $table) {
        $table->index(['academic_session_id', 'sender_user_id', 'recipient_user_id'], 'ecap_dm_session_users_idx');
      });
    }
  }

  /**
   * Supprime les colonnes et tables ajoutées.
   */
  public function down(): void
  {
    Schema::dropIfExists('ecap_direct_messages');

    if (Schema::hasColumn('ecap_staff_assignments', 'course_module_id')) {
      Schema::table('ecap_staff_assignments', function (Blueprint $table) {
        $table->dropConstrainedForeignId('course_module_id');
      });
    }
  }

  /**
   * Vérifie l'existence d'un index MySQL.
   */
  private function indexExists(string $table, string $indexName): bool
  {
    $connection = Schema::getConnection();
    $database = $connection->getDatabaseName();

    $result = $connection->select(
      'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
      [$database, $table, $indexName],
    );

    return ($result[0]->aggregate ?? 0) > 0;
  }
};
