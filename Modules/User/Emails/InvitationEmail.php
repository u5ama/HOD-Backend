<?php

namespace Modules\User\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;
    private $firstName;
    private $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($firstName, $email)
    {
        $this->firstName = $firstName;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.welcomemail')
            ->subject('Welcome on the Board')
            ->from('no-reply@herosofdigital.io')
            ->with([
                'firstName' => $this->firstName,
                'email' => $this->email,
            ]);
    }
}
