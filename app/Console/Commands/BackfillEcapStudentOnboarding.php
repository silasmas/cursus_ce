<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\User;
use App\Services\Ecap\EcapStudentOnboardingService;
use Illuminate\Console\Command;

/**
 * Complète dossiers académiques et groupes pour les inscriptions ECAP existantes.
 */
class BackfillEcapStudentOnboarding extends Command
{
  /**
   * @var string
   */
  protected $signature = 'ecap:backfill-onboarding {--user= : Identifiant utilisateur ciblé}';

  /**
   * @var string
   */
  protected $description = 'Crée les dossiers académiques et affecte les groupes de vacation manquants pour les fidèles ECAP déjà inscrits';

  /**
   * @param  EcapStudentOnboardingService  $onboardingService  Onboarding ECAP
   */
  public function handle(EcapStudentOnboardingService $onboardingService): int
  {
    $userId = $this->option('user');

    $query = Enrollment::query()
      ->with(['user', 'program'])
      ->whereHas('program', fn ($inner) => $inner->where('slug', 'ecap'))
      ->whereNotNull('academic_session_id');

    if ($userId) {
      $query->where('user_id', $userId);
    }

    $count = 0;

    foreach ($query->cursor() as $enrollment) {
      $user = $enrollment->user;

      if (! $user instanceof User) {
        continue;
      }

      $onboardingService->onboard(
        $user,
        (int) $enrollment->academic_session_id,
        (bool) $enrollment->is_online,
      );

      $count++;
    }

    $this->info("Onboarding ECAP traité pour {$count} inscription(s).");

    return self::SUCCESS;
  }
}
