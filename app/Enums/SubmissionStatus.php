<?php

namespace App\Enums;

/**
 * Statuts de remise et validation d'un TP.
 */
enum SubmissionStatus: string
{
  case Pending = 'pending';
  case Approved = 'approved';
  case Rejected = 'rejected';
}
