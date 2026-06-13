<?php

namespace App\Providers;

use App\Enums\PeriodContentType;
use App\Listeners\RecordUserLogin;
use App\Services\Ai\SafeAiProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PdroLucas\FilamentAiWriter\Contracts\AiProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(AiProvider::class, function (AiProvider $provider): SafeAiProvider {
            return new SafeAiProvider($provider);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            PeriodContentType::CourseModule->value => PeriodContentType::CourseModule->modelClass(),
            PeriodContentType::Chapter->value => PeriodContentType::Chapter->modelClass(),
            PeriodContentType::Assessment->value => PeriodContentType::Assessment->modelClass(),
        ]);

        if (request()->is('admin') || request()->is('admin/*')) {
            app()->setLocale('fr');
        }

        Event::listen(Login::class, RecordUserLogin::class);
    }
}
