<?php

namespace App\Jobs;

use App\Services\Certificate\AutoCertificateIssuer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Job périodique de génération automatique des certificats.
 */
class IssueProgramCertificatesJob implements ShouldQueue
{
  use Queueable;

  /**
   * Exécute la génération et journalise le résultat.
   */
  public function handle(AutoCertificateIssuer $issuer): void
  {
    $count = $issuer->issueForEligibleEnrollments();

    Log::info('Auto certification job executed.', [
      'issued_certificates' => $count,
    ]);
  }
}

