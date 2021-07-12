<?php

namespace App\Listeners;

use App\Events\LoggedIn;
use App\Events\MadeActivity;
use App\Http\Controllers\api\AuthController;
use App\Mail\NotifyRemainingLoginTimeMail;
use App\Models\User;
use App\Notifications\LoginNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;

class SendRemainingLoginTimeNotification implements ShouldQueue
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
        /**
         * @var $user User
         */
        $user = $event->user;
        $user->last_login_at = date('Y-m-d h:i:s');
        $user->save();
        AuthController::setLoginTimeInRedis($user);
        event(new MadeActivity($user));
        $user->notify(new LoginNotification());
    }
}
