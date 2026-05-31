<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enrichit mentorat, TP et messagerie pour le portail fidèle / mentor.
 */
return new class extends Migration
{
  /**
   * Applique les colonnes et tables nécessaires.
   */
  public function up(): void
  {
    Schema::table('mentor_profiles', function (Blueprint $table) {
      $table->text('bio')->nullable()->after('notes');
      $table->string('phone', 30)->nullable()->after('bio');
      $table->string('whatsapp', 30)->nullable()->after('phone');
      $table->string('avatar_path')->nullable()->after('whatsapp');
    });

    Schema::table('assignment_submissions', function (Blueprint $table) {
      $table->string('status', 20)->default('pending')->index()->after('submitted_at');
      $table->longText('answer_text')->nullable()->after('file_path');
      $table->foreignId('grader_id')->nullable()->after('grade')->constrained('users')->nullOnDelete();
      $table->timestamp('graded_at')->nullable()->after('grader_id');
    });

    Schema::table('mentoring_feedbacks', function (Blueprint $table) {
      $table->unsignedTinyInteger('rating')->nullable()->after('body');
      $table->string('feedback_type', 30)->default('mentee_to_mentor')->index()->after('rating');
    });

    Schema::create('mentor_messages', function (Blueprint $table) {
      $table->id();
      $table->foreignId('mentor_assignment_id')->constrained()->cascadeOnDelete();
      $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
      $table->longText('body');
      $table->timestamp('read_at')->nullable();
      $table->timestamps();

      $table->index(['mentor_assignment_id', 'created_at']);
    });
  }

  /**
   * Annule la migration.
   */
  public function down(): void
  {
    Schema::dropIfExists('mentor_messages');

    Schema::table('mentoring_feedbacks', function (Blueprint $table) {
      $table->dropColumn(['rating', 'feedback_type']);
    });

    Schema::table('assignment_submissions', function (Blueprint $table) {
      $table->dropConstrainedForeignId('grader_id');
      $table->dropColumn(['status', 'answer_text', 'graded_at']);
    });

    Schema::table('mentor_profiles', function (Blueprint $table) {
      $table->dropColumn(['bio', 'phone', 'whatsapp', 'avatar_path']);
    });
  }
};
