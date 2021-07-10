<?php

namespace App\Listeners;

use App\Events\AttemptedTooManyLogins;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetCaptchaAuthentication
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
     * @param  AttemptedTooManyLogins  $event
     * @return void
     */
    public function handle(AttemptedTooManyLogins $event)
    {
        //

    }
}
