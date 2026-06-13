<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Program;
use App\Models\SessionVacation;
use Illuminate\Database\Seeder;

/**
 * Session ECAP 20 — 2026 et vacations de référence pour la production.
 *
 * Données alignées sur la configuration actuelle PHILA-CE (Jaspe / Topaz, inscriptions publiques).
 */
class EcapProductionSessionSeeder extends Seeder
{
  /**
   * Code stable de la session de référence.
   */
  public const SESSION_CODE = '2026-V1';

  /**
   * Crée ou met à jour la session ECAP 20 et ses vacations présentiel.
   */
  public function run(): void
  {
    $program = Program::query()->where('slug', 'ecap')->first();

    if ($program === null) {
      $this->command?->warn('Programme ECAP introuvable — exécutez d\'abord FormationContentSeeder.');

      return;
    }

    $session = AcademicSession::query()->updateOrCreate(
      ['code' => self::SESSION_CODE],
      [
        'program_id' => $program->id,
        'name' => 'Session ECAP 20 — 2026',
        'generation_number' => 20,
        'starts_on' => '2026-05-11',
        'ends_on' => '2026-07-17',
        'registration_opens_at' => '2026-05-29 09:00:00',
        'registration_closes_at' => '2026-06-10 09:00:00',
        'is_active' => true,
      ],
    );

    SessionVacation::query()->updateOrCreate(
      [
        'academic_session_id' => $session->id,
        'code' => 'Matin',
      ],
      [
        'name' => 'Jaspe',
        'time_starts' => '06:00:00',
        'time_ends' => '07:30:00',
        'capacity_max' => 60,
        'sort_order' => 1,
        'is_active' => true,
      ],
    );

    SessionVacation::query()->updateOrCreate(
      [
        'academic_session_id' => $session->id,
        'code' => 'Soir',
      ],
      [
        'name' => 'Topaz',
        'time_starts' => '17:30:00',
        'time_ends' => '19:30:00',
        'capacity_max' => 100,
        'sort_order' => 20,
        'is_active' => true,
      ],
    );

    SessionVacation::query()
      ->where('academic_session_id', $session->id)
      ->whereIn('code', ['MATIN', 'SOIR'])
      ->update(['is_active' => false]);
  }
}
