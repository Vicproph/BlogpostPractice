<?php

namespace App\Listeners;

use App\Events\LoggedIn;
use App\Http\Controllers\api\AuthController;
use App\Jobs\ProcessLogout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SetLoginTimer
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
        
    }
}
