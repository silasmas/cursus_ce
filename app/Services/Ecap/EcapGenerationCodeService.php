<?php

namespace App\Services\Ecap;

use App\Models\AcademicSession;
use Illuminate\Support\Str;

/**
 * Génère les identifiants uniques des générations ECAP (préfixe ECAP).
 */
class EcapGenerationCodeService
{
  public const CODE_PREFIX = 'ECAP-';

  /**
   * Génère un code aléatoire unique (ex. ECAP-K7M2P9).
   */
  public function generateCode(): string
  {
    do {
      $code = self::CODE_PREFIX.strtoupper(Str::random(6));
    } while (AcademicSession::query()->where('code', $code)->exists());

    return $code;
  }

  /**
   * Prochain numéro ordinal de génération (affichage « nᵉ génération »).
   */
  public function nextGenerationNumber(): int
  {
    $max = AcademicSession::query()
      ->whereHas('program', fn ($query) => $query->where('slug', 'ecap'))
      ->max('generation_number');

    return ((int) $max) + 1;
  }
}
