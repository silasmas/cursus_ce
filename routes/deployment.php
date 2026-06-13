<?php

use App\Http\Controllers\System\ProductionDeployController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

$deployPath = trim((string) config('deployment.route_path', '_system/run-deploy'), '/');
$legacyPath = 'deploy/production';

$registerDeployRoute = static function (string $path) use ($deployPath): void {
  Route::match(['get', 'post'], $path, [ProductionDeployController::class, 'invoke'])
    ->middleware(['deployment.token', 'throttle:'.config('deployment.rate_limit', 6).',1'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name($path === $deployPath ? 'deploy.production' : 'deploy.production.legacy');
};

$registerDeployRoute($deployPath);

if ($deployPath !== $legacyPath) {
  $registerDeployRoute($legacyPath);
}
