<?php

/**
 * Point d'entrée HTTP direct pour initialiser le super administrateur.
 *
 * Utilisation : https://votre-domaine.com/run-bootstrap-admin.php?token=VOTRE_TOKEN
 */

declare(strict_types=1);

use App\Services\System\SuperAdminBootstrapService;
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

try {
  $result = $app->make(SuperAdminBootstrapService::class)->run();

  http_response_code($result['success'] ? 200 : 500);
  echo json_encode([
    'success' => $result['success'],
    'message' => $result['success']
      ? 'Super administrateur initialisé avec succès.'
      : 'Initialisation interrompue : une étape a échoué.',
    'entrypoint' => 'run-bootstrap-admin.php',
    'admin' => $result['admin'],
    'login_url' => url('/admin/login'),
    'steps' => $result['steps'],
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (\InvalidArgumentException $exception) {
  http_response_code(422);
  echo json_encode([
    'success' => false,
    'message' => $exception->getMessage(),
    'entrypoint' => 'run-bootstrap-admin.php',
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (\Throwable $exception) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'message' => $exception->getMessage(),
    'entrypoint' => 'run-bootstrap-admin.php',
  ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
