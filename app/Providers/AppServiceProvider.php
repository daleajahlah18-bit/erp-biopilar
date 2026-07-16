<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrapFive();

        \Carbon\Carbon::setToStringFormat('d M Y, H:i');

        Password::defaults(function () {
            $rule = Password::min(8)->letters()->mixedCase()->numbers()->symbols();
            return $rule;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
