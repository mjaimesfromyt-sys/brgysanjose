<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Laravel ships Tailwind-flavoured pagination markup by default, which
        // renders unstyled in this Bootstrap app (see the news list).
        Paginator::useBootstrapFive();
    }
}
