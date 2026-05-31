<?php

namespace App\Enums;

/**
 * Types d'évaluation pédagogique (quiz, TP, examen).
 */
enum AssessmentType: string
{
  case Quiz = 'quiz';
  case Tp = 'tp';
  case Exam = 'exam';
}
