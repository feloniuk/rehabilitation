<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFour();
        Carbon::setLocale('uk');

        // Set default string length for MySQL
        \Illuminate\Database\Schema\Builder::defaultStringLength(191);


        URL::forceRootUrl(config('app.url'));
    }
}