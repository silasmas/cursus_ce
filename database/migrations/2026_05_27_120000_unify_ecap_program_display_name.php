<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  /**
   * Unifie le libellé du cursus ECAP / Apollos CE en base.
   */
  public function up(): void
  {
    DB::table('programs')
      ->where('slug', 'ecap')
      ->update([
        'name' => 'ECAP',
        'description' => 'Apollos CE — École d\'Apolos : formation biblique structurée (cursus ECAP).',
        'updated_at' => now(),
      ]);
  }

  /**
   * Ne restaure pas l'ancien libellé.
   */
  public function down(): void
  {
    //
  }
};
