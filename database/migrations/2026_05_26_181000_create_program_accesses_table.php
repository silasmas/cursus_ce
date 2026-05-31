<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée la table des accès par cursus pour chaque utilisateur.
   */
  public function up(): void
  {
    Schema::create('program_accesses', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('program_id')->constrained()->cascadeOnDelete();
      $table->string('status', 30)->index();
      $table->string('source', 30)->index();
      $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamp('validated_at')->nullable();
      $table->timestamps();

      $table->unique(['user_id', 'program_id']);
      $table->index(['user_id', 'status']);
    });
  }

  /**
   * Supprime la table.
   */
  public function down(): void
  {
    Schema::dropIfExists('program_accesses');
  }
};

