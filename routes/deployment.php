<?php

use App\Http\Controllers\System\ProductionDeployController;
use Illuminate\Support\Facades\Route;

$deployPath = trim((string) config('deployment.route_path', 'deploy/production'), '/');

Route::middleware(['deployment.token', 'throttle:'.config('deployment.rate_limit', 6).',1'])
  ->group(function () use ($deployPath): void {
    Route::get($deployPath, [ProductionDeployController::class, 'info'])->name('deploy.production.info');
    Route::post($deployPath, [ProductionDeployController::class, 'invoke'])->name('deploy.production');
  });
