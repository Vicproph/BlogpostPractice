<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            'App\Listeners\AssignRoles'
        ],
        'App\Events\Registered'=>[
            "App\Listeners\SendEmailVerificationNotification"
        ],
        'App\Events\LoggedIn'=>[
            "App\Listeners\SendRemainingLoginTimeNotification"
        ],
        'App\Events\PostUnapproved'=>[
            "App\Listeners\SendPostRejectionNotification"
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
