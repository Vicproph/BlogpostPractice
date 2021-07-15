<?php

namespace App\Jobs;

use App\Http\Controllers\api\AuthController;
use App\Listeners\SendRemainingLoginTimeNotification;
use App\Mail\NotifyRemainingLoginTimeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessLogout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // this job makes sure that the user will be logged out according to the MAX_LOGIN_TIME (in minutes)

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user;
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /**
         * @var User $user
         */
        $user = $this->user;
        $user->tokens()->delete();
    }
}
