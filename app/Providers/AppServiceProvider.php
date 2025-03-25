<?php

namespace App\Providers;

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
        //
        ini_set('xdebug.var_display_max_depth', '-1');
        ini_set('xdebug.var_display_max_children', '-1');
        ini_set('xdebug.var_display_max_data', '-1');
    }
}
