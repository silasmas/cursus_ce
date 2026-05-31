<?php

namespace App\Enums;

/**
 * Rôles des acteurs de vacation ECAP (M6).
 */
enum EcapVacationRole: string
{
  case Teacher = 'teacher';
  case Supervisor = 'supervisor';
  case Moderator = 'moderator';
  case Inspector = 'inspector';

  /**
   * Libellé français du rôle.
   */
  public function label(): string
  {
    return match ($this) {
      self::Teacher => 'Enseignant',
      self::Supervisor => 'Superviseur',
      self::Moderator => 'Modérateur',
      self::Inspector => 'Inspecteur',
    };
  }

  /**
   * Description courte pour l'administration.
   */
  public function description(): string
  {
    return match ($this) {
      self::Teacher => 'Enseigne, répond aux questions et dépose le TP modèle.',
      self::Supervisor => 'Gère la vacation, corrige les TP remis et répond aux questions qui lui sont adressées.',
      self::Moderator => 'Facilite les TP, corrige les grands TP et valide le passage de module.',
      self::Inspector => 'Rédige les rapports journaliers sur les dirigeants de vacation.',
    };
  }

  /**
   * Options pour les listes déroulantes Filament.
   *
   * @return array<string, string>
   */
  public static function options(): array
  {
    $options = [];

    foreach (self::cases() as $case) {
      $options[$case->value] = $case->label();
    }

    return $options;
  }
}
