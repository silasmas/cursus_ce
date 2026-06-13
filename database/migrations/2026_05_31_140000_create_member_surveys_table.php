<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée la table des sondages de satisfaction fidèle.
   */
  public function up(): void
  {
    Schema::create('member_surveys', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->unsignedTinyInteger('satisfaction')->nullable();
      $table->unsignedTinyInteger('nps_score')->nullable();
      $table->text('comment')->nullable();
      $table->unsignedSmallInteger('weeks_since_enrollment')->nullable();
      $table->timestamp('submitted_at')->nullable();
      $table->timestamp('snoozed_until')->nullable();
      $table->timestamps();

      $table->unique('user_id');
    });
  }

  /**
   * Supprime la table des sondages fidèle.
   */
  public function down(): void
  {
    Schema::dropIfExists('member_surveys');
  }
};
