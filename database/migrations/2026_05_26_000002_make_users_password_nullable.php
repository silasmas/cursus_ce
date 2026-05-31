<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Rend le mot de passe nullable pour les comptes connectés uniquement par OTP.
   */
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('password')->nullable()->change();
    });
  }

  /**
   * Restaure le mot de passe obligatoire.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('password')->nullable(false)->change();
    });
  }
};
