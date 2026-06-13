<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée la table de paramètres globaux mentorat.
   */
  public function up(): void
  {
    Schema::create('mentor_settings', function (Blueprint $table) {
      $table->id();
      $table->json('visible_channels')->nullable();
      $table->boolean('zoom_auto_create_link')->default(false);
      $table->boolean('notify_with_email')->default(true);
      $table->boolean('notify_with_sound')->default(true);
      $table->boolean('notify_with_blink')->default(true);
      $table->string('google_meet_help')->nullable();
      $table->string('whatsapp_help')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Supprime la table.
   */
  public function down(): void
  {
    Schema::dropIfExists('mentor_settings');
  }
};

