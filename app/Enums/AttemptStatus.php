<?php

namespace App\Enums;

/**
 * Statuts d'une tentative de test.
 */
enum AttemptStatus: string
{
  case InProgress = 'in_progress';
  case Submitted = 'submitted';
  case Graded = 'graded';
}
