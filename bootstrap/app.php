<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'run-production-deploy',
            '_system/run-deploy',
            'deploy/production',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'mentor' => \App\Http\Middleware\EnsureUserIsMentor::class,
            'ecap.staff' => \App\Http\Middleware\EnsureEcapVacationStaff::class,
            'deployment.token' => \App\Http\Middleware\ValidateDeploymentToken::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $exception, \Illuminate\Http\Request $request) {
            if ($exception->getStatusCode() !== 403) {
                return null;
            }

            if ($request->is('ecap/acteurs', 'ecap/acteurs/*')) {
                return \Inertia\Inertia::render('Ecap/AccessDenied', [
                    'title' => 'Accès non autorisé',
                    'feature' => 'Espace acteurs ECAP',
                    'requiredRole' => 'Acteur ECAP',
                    'yourRoles' => [],
                    'hint' => $exception->getMessage() ?: 'Vous n\'avez pas les droits pour cette action.',
                    'backUrl' => route('ecap.staff.questions.index'),
                ])->toResponse($request)->setStatusCode(403);
            }

            if ($request->header('X-Inertia')) {
                return \Inertia\Inertia::render('Ecap/AccessDenied', [
                    'title' => 'Accès refusé',
                    'feature' => null,
                    'requiredRole' => null,
                    'yourRoles' => [],
                    'hint' => $exception->getMessage() ?: 'Vous n\'avez pas la permission d\'accéder à cette page.',
                    'backUrl' => route('dashboard'),
                ])->toResponse($request)->setStatusCode(403);
            }

            return null;
        });
    })->create();
