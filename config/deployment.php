<?php

/**
 * Déploiement HTTP sécurisé (migrations, seeders, Shield) pour la production.
 */
return [

  /*
  |--------------------------------------------------------------------------
  | Jeton secret
  |--------------------------------------------------------------------------
  |
  | Définissez DEPLOYMENT_TOKEN dans .env (chaîne longue aléatoire).
  | Envoyez-le via l'en-tête X-Deployment-Token ou Authorization: Bearer {token}.
  |
  */
  'token' => env('DEPLOYMENT_TOKEN'),

  /*
  |--------------------------------------------------------------------------
  | Route HTTP
  |--------------------------------------------------------------------------
  */
  'route_path' => env('DEPLOYMENT_ROUTE', 'run-production-deploy'),

  /*
  |--------------------------------------------------------------------------
  | Limite de requêtes (par minute, par IP)
  |--------------------------------------------------------------------------
  */
  'rate_limit' => (int) env('DEPLOYMENT_RATE_LIMIT', 6),

  /*
  |--------------------------------------------------------------------------
  | Seeder exécuté par la route de déploiement complet
  |--------------------------------------------------------------------------
  */
  'production_seeder_key' => env('DEPLOYMENT_SEEDER_KEY', 'production-starter'),

  /*
  |--------------------------------------------------------------------------
  | Super administrateur initial (route bootstrap)
  |--------------------------------------------------------------------------
  */
  'bootstrap_admin' => [
    'email' => env('BOOTSTRAP_ADMIN_EMAIL', 'admin@ce.church'),
    'password' => env('BOOTSTRAP_ADMIN_PASSWORD', 'silasmas'),
    'name' => env('BOOTSTRAP_ADMIN_NAME', 'Administrateur CE'),
  ],

  'bootstrap_route_path' => env('BOOTSTRAP_ADMIN_ROUTE', 'run-bootstrap-admin'),

];
