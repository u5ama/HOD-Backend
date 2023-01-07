<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class SendInvitation extends Mailable
{
    use Queueable, SerializesModels;
    private $firstName;
    private $email;
    private $url;
    /**
     * @var string
     */

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($firstName, $email)
    {
        $this->firstName = $firstName;
        $this->email = $email;
        $this->url = frontUrl();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('i am here 1');
        Log::info($this->firstName);
        Log::info('i am here 2');
        Log::info($this->email);
        Log::info('i am here 3');
        Log::info($this->url);

        return $this->view('email.welcomemail')
            ->subject('Welcome on the Board')
            ->from('no-reply@herosofdigital.io')
            ->with([
                'firstName' => $this->firstName,
                'email' => $this->email,
                'url' => $this->url
            ]);
    }
}
