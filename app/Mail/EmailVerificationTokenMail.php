<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationToken;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verificationToken)
    {
        //
        $this->verificationToken = $verificationToken;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.email-verification')->with(['token'=>$this->verificationToken]);
    }
}
