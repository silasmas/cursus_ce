<?php

namespace App\Services\ProgramAccess;

use App\Models\ProgramAccess;
use Illuminate\Support\Facades\Auth;

/**
 * Gère les transitions d'état booléens d'un accès cursus (mutuellement exclusifs).
 */
class ProgramAccessStateService
{
  /**
   * Remet tous les indicateurs à false.
   *
   * @return array<string, bool>
   */
  private function blankFlags(): array
  {
    return [
      'is_pending' => false,
      'is_open' => false,
      'is_completed' => false,
      'is_waived' => false,
      'needs_admin_validation' => false,
    ];
  }

  /**
   * Accès en attente (cursus fermé ou non encore ouvert).
   */
  public function setPending(ProgramAccess $access): ProgramAccess
  {
    return $this->apply($access, ['is_pending' => true]);
  }

  /**
   * Accès ouvert au parcours en ligne.
   */
  public function setOpen(ProgramAccess $access, ?string $source = null): ProgramAccess
  {
    return $this->apply($access, ['is_open' => true], $source);
  }

  /**
   * Cursus acquis (validation admin ou parcours terminé).
   */
  public function setCompleted(ProgramAccess $access, ?string $source = 'admin_validated'): ProgramAccess
  {
    return $this->apply($access, ['is_completed' => true], $source, recordValidation: true);
  }

  /**
   * Dispense administrative.
   */
  public function setWaived(ProgramAccess $access): ProgramAccess
  {
    return $this->apply($access, ['is_waived' => true], 'admin_validated', recordValidation: true);
  }

  /**
   * Fidèle a déclaré avoir déjà suivi le cursus — validation admin requise.
   */
  public function setNeedsAdminValidation(ProgramAccess $access, ?string $source = 'registration'): ProgramAccess
  {
    return $this->apply($access, ['needs_admin_validation' => true], $source);
  }

  /**
   * Libellé lisible du statut courant.
   */
  public function label(ProgramAccess $access): string
  {
    return match (true) {
      $access->is_waived => 'Dispensé',
      $access->is_completed => 'Acquis',
      $access->needs_admin_validation => 'Déclaré (à valider)',
      $access->is_open => 'Ouvert',
      $access->is_pending => 'En attente',
      default => 'En attente',
    };
  }

  /**
   * Code court pour filtres et API (compatibilité affichage).
   */
  public function legacyCode(ProgramAccess $access): string
  {
    return match (true) {
      $access->is_waived => 'waived',
      $access->is_completed => 'completed',
      $access->needs_admin_validation => 'declared_completed',
      $access->is_open => 'open',
      default => 'pending',
    };
  }

  /**
   * Applique un jeu d'indicateurs exclusifs et persiste.
   *
   * @param  array<string, bool>  $activeFlags  Un seul indicateur à true
   */
  private function apply(
    ProgramAccess $access,
    array $activeFlags,
    ?string $source = null,
    bool $recordValidation = false,
  ): ProgramAccess {
    $payload = array_merge($this->blankFlags(), $activeFlags);

    if ($source !== null) {
      $payload['source'] = $source;
    }

    if ($recordValidation) {
      $payload['validated_by_user_id'] = Auth::id();
      $payload['validated_at'] = now();
    }

    $access->update($payload);

    return $access->refresh();
  }
}
