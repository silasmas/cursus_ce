<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Crée la table des codes OTP pour la connexion par e-mail.
   */
  public function up(): void
  {
    Schema::create('login_otps', function (Blueprint $table) {
      $table->id();
      $table->string('email')->index();
      $table->string('code', 6);
      $table->timestamp('expires_at');
      $table->timestamp('used_at')->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->timestamps();
    });
  }

  /**
   * Supprime la table login_otps.
   */
  public function down(): void
  {
    Schema::dropIfExists('login_otps');
  }
};
