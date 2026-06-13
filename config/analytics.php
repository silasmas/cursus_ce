<?php

/**
 * Analytics du portail Inertia (Plausible ou Google Analytics).
 *
 * Le panneau admin (/admin) est exclu du tracking côté frontend.
 */
return [

  'driver' => env('ANALYTICS_DRIVER', 'none'),

  'plausible' => [
    'domain' => env('PLAUSIBLE_DOMAIN'),
    'script_url' => env('PLAUSIBLE_SCRIPT_URL', 'https://plausible.io/js/script.js'),
  ],

  'ga' => [
    'measurement_id' => env('GA_MEASUREMENT_ID'),
  ],

];
