<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée la table de suivi des opérations de déploiement exécutées depuis l'admin.
   */
  public function up(): void
  {
    Schema::create('deployment_operations', function (Blueprint $table): void {
      $table->id();
      $table->string('type');
      $table->string('status');
      $table->string('command');
      $table->json('parameters')->nullable();
      $table->longText('output')->nullable();
      $table->unsignedSmallInteger('exit_code')->nullable();
      $table->foreignId('executed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamp('started_at');
      $table->timestamp('finished_at')->nullable();
      $table->timestamps();

      $table->index(['type', 'status']);
      $table->index('started_at');
    });
  }

  /**
   * Supprime la table de suivi des opérations de déploiement.
   */
  public function down(): void
  {
    Schema::dropIfExists('deployment_operations');
  }
};
