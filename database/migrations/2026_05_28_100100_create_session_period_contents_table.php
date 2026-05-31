<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée les affectations de contenus pédagogiques aux périodes ECAP.
   */
  public function up(): void
  {
    Schema::create('session_period_contents', function (Blueprint $table) {
      $table->id();
      $table->foreignId('session_period_id')->constrained()->cascadeOnDelete();
      $table->string('content_type', 32);
      $table->unsignedBigInteger('content_id');
      $table->unsignedSmallInteger('sort_order')->default(0);
      $table->string('label')->nullable();
      $table->timestamps();

      $table->unique(
        ['session_period_id', 'content_type', 'content_id'],
        'spc_period_content_uq',
      );
    });
  }

  /**
   * Supprime la table des affectations.
   */
  public function down(): void
  {
    Schema::dropIfExists('session_period_contents');
  }
};
