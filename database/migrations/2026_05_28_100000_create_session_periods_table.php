<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée les périodes (cours, TFE, défenses) d'une génération ECAP.
   */
  public function up(): void
  {
    Schema::create('session_periods', function (Blueprint $table) {
      $table->id();
      $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
      $table->string('type', 32);
      $table->string('name')->nullable();
      $table->date('starts_on');
      $table->date('ends_on');
      $table->unsignedSmallInteger('sort_order')->default(0);
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->index(['academic_session_id', 'sort_order'], 'sp_session_sort_idx');
    });
  }

  /**
   * Supprime la table des périodes.
   */
  public function down(): void
  {
    Schema::dropIfExists('session_periods');
  }
};
