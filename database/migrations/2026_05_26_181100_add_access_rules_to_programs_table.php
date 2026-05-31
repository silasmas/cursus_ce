<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Ajoute des règles d'accès configurables aux cursus.
   */
  public function up(): void
  {
    Schema::table('programs', function (Blueprint $table) {
      $table->boolean('is_mandatory')->default(false)->after('type');
      $table->boolean('is_open')->default(true)->after('is_mandatory');
      $table->boolean('optional_at_registration')->default(true)->after('is_open');
      $table->timestamp('scheduled_open_at')->nullable()->after('optional_at_registration');

      $table->index(['is_open', 'is_mandatory']);
    });
  }

  /**
   * Retire les champs ajoutés.
   */
  public function down(): void
  {
    Schema::table('programs', function (Blueprint $table) {
      $table->dropIndex(['is_open', 'is_mandatory']);
      $table->dropColumn([
        'is_mandatory',
        'is_open',
        'optional_at_registration',
        'scheduled_open_at',
      ]);
    });
  }
};

