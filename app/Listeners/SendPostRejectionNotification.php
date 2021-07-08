<?php

namespace App\Listeners;

use App\Events\PostUnapproved;
use App\Models\User;
use App\Notifications\PostRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPostRejectionNotification implements ShouldQueue
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
     * @param  PostUnapproved  $event
     * @return void
     */
    public function handle(PostUnapproved $event)
    {
        //
        /**
         * @var $user User
         */
        $user = $event->post->user;
        $user->notify(new PostRejectedNotification($event->post));
    }
}
