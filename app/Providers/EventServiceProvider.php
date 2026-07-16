<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            $event->user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip()
            ]);
            activity()
                ->causedBy($event->user)
                ->event('login')
                ->log('User logged in');
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                activity()
                    ->causedBy($event->user)
                    ->event('logout')
                    ->log('User logged out');
            }
        });
        
        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            activity()
                ->event('failed_login')
                ->log('Failed login attempt for email: ' . $event->credentials['email']);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
