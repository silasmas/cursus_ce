<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée la table des connexions (statistiques appareils).
   */
  public function up(): void
  {
    Schema::create('login_events', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('guard', 32)->index();
      $table->string('device_type', 16)->index();
      $table->string('browser', 64)->nullable();
      $table->string('platform', 64)->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->string('user_agent', 512)->nullable();
      $table->timestamp('logged_in_at')->index();
      $table->timestamps();
    });
  }

  /**
   * Supprime la table des connexions.
   */
  public function down(): void
  {
    Schema::dropIfExists('login_events');
  }
};
