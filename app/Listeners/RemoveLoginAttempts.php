<?php

namespace App\Listeners;

use App\Events\LoggedIn;
use App\Models\LoginAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemoveLoginAttempts
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LoggedIn  $event
     * @return void
     */
    public function handle(LoggedIn $event)
    {
        //
        LoginAttempt::deleteLoginAttempts($event->ip);
    }
}
