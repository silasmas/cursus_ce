<?php

namespace App\Exceptions;

use Exception;

/**
 * Erreur métier affichable à l'administrateur lors d'un appel à l'assistant IA.
 */
class AiWriterException extends Exception
{
  /**
   * @param  string  $title  Titre de la notification Filament
   * @param  string  $message  Message détaillé pour l'utilisateur
   * @param  int  $code  Code HTTP ou interne
   */
  public function __construct(
    public readonly string $title,
    string $message,
    int $code = 0,
  ) {
    parent::__construct($message, $code);
  }
}
