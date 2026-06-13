<?php

/**
 * Point d'entrée HTTP direct pour le déploiement production (hébergement mutualisé).
 *
 * Utilisation : https://votre-domaine.com/run-production-deploy.php?token=VOTRE_TOKEN
 * Étapes optionnelles : &steps=migrate,seed,shield
 */

declare(strict_types=1);

use App\Services\System\ProductionDeployRunner;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make(ConsoleKernel::class)->bootstrap();

header('Content-Type: application/json; charset=utf-8');

$configuredToken = config('deployment.token');
$providedToken = isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : '';

if (! is_string($configuredToken) || $configuredToken === '') {
  http_response_code(503);
  echo json_encode([
    'success' => false,
    'message' => 'DEPLOYMENT_TOKEN non configuré dans le fichier .env.',
  ], JSON_UNESCAPED_UNICODE);

  exit;
}

if ($providedToken === '' || ! hash_equals($configuredToken, $providedToken)) {
  http_response_code(401);
  echo json_encode([
    'success' => false,
    'message' => 'Jeton invalide ou absent. Ajoutez ?token=VOTRE_TOKEN à l\'URL.',
  ], JSON_UNESCAPED_UNICODE);

  exit;
}

$steps = null;

if (isset($_GET['steps']) && is_string($_GET['steps']) && $_GET['steps'] !== '') {
  $steps = array_values(array_filter(array_map('trim', explode(',', $_GET['steps']))));
}

try {
  $result = $app->make(ProductionDeployRunner::class)->run($steps);

  http_response_code($result['success'] ? 200 : 500);
  echo json_encode([
    'success' => $result['success'],
    'message' => $result['success']
      ? 'Déploiement production terminé avec succès.'
      : 'Déploiement interrompu : une étape a échoué.',
    'entrypoint' => 'run-production-deploy.php',
    'steps' => $result['steps'],
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => $exception->getMessage(),
    'entrypoint' => 'run-production-deploy.php',
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
