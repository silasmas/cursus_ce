<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Vacations proposées à l'inscription présentiel ECAP.
   */
  public function up(): void
  {
    Schema::create('session_vacations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->string('code')->nullable();
      $table->unsignedSmallInteger('capacity_max')->nullable();
      $table->boolean('is_active')->default(true);
      $table->unsignedSmallInteger('sort_order')->default(0);
      $table->timestamps();

      $table->index(['academic_session_id', 'is_active']);
    });
  }

  /**
   * Supprime la table des vacations de session.
   */
  public function down(): void
  {
    Schema::dropIfExists('session_vacations');
  }
};
