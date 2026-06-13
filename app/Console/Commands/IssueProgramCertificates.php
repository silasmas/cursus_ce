<?php

namespace App\Console\Commands;

use App\Services\Certificate\AutoCertificateIssuer;
use Illuminate\Console\Command;

/**
 * Génère les certificats pour les fidèles ayant terminé leur cursus.
 */
class IssueProgramCertificates extends Command
{
  /**
   * @var string
   */
  protected $signature = 'certificates:issue-auto';

  /**
   * @var string
   */
  protected $description = 'Génère automatiquement les certificats des inscriptions terminées';

  /**
   * Exécute la génération en mode commande.
   */
  public function handle(AutoCertificateIssuer $issuer): int
  {
    $count = $issuer->issueForEligibleEnrollments();
    $this->info("{$count} certificat(s) généré(s).");

    return self::SUCCESS;
  }
}

