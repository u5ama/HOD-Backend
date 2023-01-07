<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendResetPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $link;
    public $first_name;
    public $last_name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link, $first_name, $last_name)
    {
        $this->link = $link;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reset Password') ->from('no-reply@herosofdigital.io')->view('email.forgetmail');
    }
}
