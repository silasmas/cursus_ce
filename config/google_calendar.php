<?php

/**
 * Google Calendar API — création automatique de liens Google Meet.
 *
 * Obtenir un refresh token :
 * 1. Console Google Cloud → activer l'API Calendar
 * 2. Créer des identifiants OAuth (application Web)
 * 3. Autoriser le scope https://www.googleapis.com/auth/calendar
 * 4. Échanger le code d'autorisation contre un refresh_token
 */
return [
  'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID'),
  'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),
  'refresh_token' => env('GOOGLE_CALENDAR_REFRESH_TOKEN'),
  'calendar_id' => env('GOOGLE_CALENDAR_ID', 'primary'),
  'timezone' => env('GOOGLE_CALENDAR_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
];
