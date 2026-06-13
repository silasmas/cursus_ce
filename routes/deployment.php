<?php

use App\Http\Controllers\System\ProductionDeployController;
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
