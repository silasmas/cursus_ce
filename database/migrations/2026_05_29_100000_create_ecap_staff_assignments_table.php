<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Affectations des acteurs de vacation ECAP (enseignant, superviseur, modérateur).
   */
  public function up(): void
  {
    Schema::create('ecap_staff_assignments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
      $table->foreignId('session_vacation_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('role', 32);
      $table->boolean('is_active')->default(true);
      $table->text('notes')->nullable();
      $table->timestamps();

      $table->index(['academic_session_id', 'role', 'is_active']);
      $table->unique(
        ['academic_session_id', 'user_id', 'role', 'session_vacation_id'],
        'ecap_staff_assignments_unique',
      );
    });
  }

  /**
   * Supprime la table des affectations ECAP.
   */
  public function down(): void
  {
    Schema::dropIfExists('ecap_staff_assignments');
  }
};
