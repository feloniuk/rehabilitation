<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Observers\AppointmentObserver;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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

        // Реєстрація Observer для аудиту записів
        Appointment::observe(AppointmentObserver::class);
    }
}
