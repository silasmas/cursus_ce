<?php

namespace App\Providers;

use App\Enums\PeriodContentType;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
