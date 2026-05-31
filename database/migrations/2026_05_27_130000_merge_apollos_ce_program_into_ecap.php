<?php

use App\Services\Program\MergeApollosCeProgramService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
  /**
   * Fusionne le programme « apollos-ce » dans « ecap » et supprime le doublon.
   */
  public function up(): void
  {
    app(MergeApollosCeProgramService::class)->merge();
  }

  /**
   * Non réversible automatiquement.
   */
  public function down(): void
  {
    //
  }
};
