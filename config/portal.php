<?php

/**
 * Paramètres du portail fidèle (Inertia).
 */
return [

  'member_survey' => [
    'enabled' => env('MEMBER_SURVEY_ENABLED', true),
    'weeks_after_enrollment' => (int) env('MEMBER_SURVEY_WEEKS_AFTER_ENROLLMENT', 4),
    'snooze_days' => (int) env('MEMBER_SURVEY_SNOOZE_DAYS', 7),
  ],

];
