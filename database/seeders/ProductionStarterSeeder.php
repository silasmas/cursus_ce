<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Pack de démarrage production : cursus, session ECAP, calendrier, documents et permissions.
 */
class ProductionStarterSeeder extends Seeder
{
  /**
   * Exécute les seeders idempotents recommandés pour une mise en production.
   */
  public function run(): void
  {
    $this->call([
      FormationContentSeeder::class,
      EcapProductionSessionSeeder::class,
      EcapSession20CalendarSeeder::class,
      LegalDocumentSeeder::class,
      AdminPermissionsSeeder::class,
    ]);
  }
}
