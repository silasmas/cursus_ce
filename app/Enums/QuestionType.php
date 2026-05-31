<?php

namespace App\Enums;

/**
 * Types de questions dans un test (QCM ou réponse rédigée).
 */
enum QuestionType: string
{
  case Mcq = 'mcq';
  case Written = 'written';
}
