<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Log;

class newCustomerAdded extends Mailable
{
    use Queueable, SerializesModels;

    protected $firstName;
    protected $phoneNumber;
    protected $email;
    protected $fieldName;

    /**
     * Create a new message instance.
     *
     * @param $firstName
     * @param $phoneNumber
     * @param $email
     * @param $fieldName
     */
    public function __construct($firstName, $phoneNumber, $email, $fieldName)
    {
        $this->firstName = $firstName;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
        $this->fieldName = $fieldName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info($this->firstName);
        Log::info($this->email);
        Log::info('i am here');
        Log::info($this->fieldName);
        return $this->view('email.customerMail')
            ->subject('New Customer Added')
            ->from('no-reply@heroesofdigital.com')
            ->with([
                'name' => $this->firstName,
                'phone' => $this->phoneNumber,
                'email' => $this->email,
                'fieldName' => $this->fieldName,
        ]);
    }
}
