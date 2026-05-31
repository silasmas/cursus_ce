<?php

namespace App\Services\Ecap;

use App\Services\Public\RegistrationAvailabilityService;

/**
 * Expose les données publiques d'une génération ECAP (countdown si inscriptions ouvertes).
 */
class EcapSessionPublicService
{
  /**
   * @param  RegistrationAvailabilityService  $registrationAvailability  Fenêtre d'inscription
   */
  public function __construct(
    private readonly RegistrationAvailabilityService $registrationAvailability,
  ) {}

  /**
   * Retourne le bandeau countdown uniquement pendant la fenêtre d'inscription ouverte.
   *
   * @return array<string, mixed>|null
   */
  public function featuredSessionPayload(): ?array
  {
    return $this->registrationAvailability->ecapCountdownPayload();
  }
}
