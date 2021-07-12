<?php

namespace App\Listeners;

use App\Events\MadeActivity;
use App\Http\Controllers\api\UserController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReportActivity
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
     * @param  MadeActivity  $event
     * @return void
     */
    public function handle(MadeActivity $event)
    {
        //
        $user = $event->user;
        $user->last_activity_at = date('Y-m-d h:i:s');
        UserController::setLastActivityTimeInRedis($user);
        $user->save();
    }
}
