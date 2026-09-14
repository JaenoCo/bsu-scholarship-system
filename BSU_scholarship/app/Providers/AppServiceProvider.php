<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Use one framework-independent paginator across Central, SFAO, and Student
        // pages. The project does not load Bootstrap's pagination component styles.
        Paginator::defaultView('vendor.pagination.custom');

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
