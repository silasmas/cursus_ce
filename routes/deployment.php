<?php

use App\Http\Controllers\System\ProductionDeployController;
use App\Http\Controllers\System\SuperAdminBootstrapController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

$deployPaths = array_values(array_unique(array_filter([
  trim((string) config('deployment.route_path', 'run-production-deploy'), '/'),
  'run-production-deploy',
  '_system/run-deploy',
  'deploy/production',
])));

foreach ($deployPaths as $index => $path) {
  Route::match(['get', 'post'], $path, [ProductionDeployController::class, 'invoke'])
    ->middleware(['deployment.token', 'throttle:'.config('deployment.rate_limit', 6).',1'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name($index === 0 ? 'deploy.production' : 'deploy.production.'.$index);
}

$bootstrapPaths = array_values(array_unique(array_filter([
  trim((string) config('deployment.bootstrap_route_path', 'run-bootstrap-admin'), '/'),
  'run-bootstrap-admin',
])));

foreach ($bootstrapPaths as $index => $path) {
  Route::match(['get', 'post'], $path, [SuperAdminBootstrapController::class, 'invoke'])
    ->middleware(['deployment.token', 'throttle:'.config('deployment.rate_limit', 6).',1'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name($index === 0 ? 'bootstrap.super-admin' : 'bootstrap.super-admin.'.$index);
}
