<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            // Super Admin Bypass
            if ($user->hasRole('Administrator')) {
                return true;
            }

            // Development Fallback: If roles are not assigned or permissions aren't seeded yet
            if ($user->roles()->count() === 0 || \Spatie\Permission\Models\Permission::count() === 0) {
                return true;
            }
        });
    }
}
