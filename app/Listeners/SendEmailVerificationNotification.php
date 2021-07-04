<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Notifications\VerificationTokenNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SendEmailVerificationNotification
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        /*
         * if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {
            $event->user->sendEmailVerificationNotification();
        }
        */

        $verificationToken = $this->generateVerificationToken($event); // این به کاربر ایمیل می شود
        /**
         * @var $user User
         */
        $user = $event->user;
        $user->notify(new VerificationTokenNotification($verificationToken));
    }

    private function generateVerificationToken($event)
    {
        $verificationToken = Str::random();
        $this->storeTokenInDatabase($verificationToken, $event);
        return $verificationToken;
    }

    private function setExpirationDate() // یک datetime ای برای منقضی شدن کد تایید تولید می کند
    {
        $datetime = new \DateTime();
        $interval = new \DateInterval('P1D');
        return $datetime->add($interval);
    }

    private function storeTokenInDatabase($verificationToken, $event)
    {
        $hashedVerificationToken = Hash::make($verificationToken); // این در دیتابیس ذخیره می شود
        $expiresOn = $this->setExpirationDate();

        $this->deletePreviousToken($event); // ممکن است کاربر چند دفعه در خواست تایید ایمیل کند که کد قبلی باید منقضی شود

        $emailVerficationToken = new EmailVerificationToken([
            'token' => $hashedVerificationToken,
            'user_id' => $event->user->id,
            'expires_on' => $expiresOn
        ]);
        $emailVerficationToken->save();
    }

    private function deletePreviousToken($event)
    {
        $user = $event->user;
        /**
         * @var $user User
         */
        $user->emailVerificationToken()->delete();
    }
}
