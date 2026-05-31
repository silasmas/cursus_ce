<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rendez-vous en ligne mentor / mentoré et notifications portail.
 */
return new class extends Migration
{
  /**
   * Crée les tables rendez-vous et notifications.
   */
  public function up(): void
  {
    Schema::create('mentor_appointments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('mentor_assignment_id')->constrained()->cascadeOnDelete();
      $table->timestamp('scheduled_at');
      $table->string('channel', 30);
      $table->string('meeting_url')->nullable();
      $table->text('notes')->nullable();
      $table->string('status', 20)->default('scheduled');
      $table->timestamps();

      $table->index(['mentor_id', 'scheduled_at']);
    });

    Schema::create('portal_notifications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('type', 50)->index();
      $table->string('title');
      $table->text('body');
      $table->string('action_url')->nullable();
      $table->string('action_label')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamp('read_at')->nullable();
      $table->timestamps();

      $table->index(['user_id', 'read_at', 'created_at']);
    });
  }

  /**
   * Supprime les tables créées.
   */
  public function down(): void
  {
    Schema::dropIfExists('portal_notifications');
    Schema::dropIfExists('mentor_appointments');
  }
};
