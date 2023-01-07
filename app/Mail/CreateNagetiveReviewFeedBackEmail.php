<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Log;

class CreateNagetiveReviewFeedBackEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;

    protected $messages;

    protected $dateSent;

    protected $customerEmail;

    protected $domainName;

    protected $domainHeading;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name ,$messages, $dateSent,$customerEmail)
    {

        $this->name = $name;
        $this->messages = $messages;
        $this->dateSent = $dateSent;
        $this->customerEmail = $customerEmail;
        $this->domainHeading = getDomainHeading();
        $this->domainName = getDomainName();


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->view('email.negativefeedback')

            ->subject('['.$this->domainHeading.'] Negative Feedback Received')

            ->from("no-reply@$this->domainName.com",$this->domainHeading)
            ->with([
                'name' => $this->name,
                'messages' => $this->messages,
                'dateSent' => $this->dateSent,
                'email' => $this->customerEmail,
                ]);
    }
}
