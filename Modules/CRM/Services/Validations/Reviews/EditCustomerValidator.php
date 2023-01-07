<?php

namespace Modules\CRM\Services\Validations\Reviews;

use App\Services\Validations\LaravelValidator;

class EditCustomerValidator extends LaravelValidator
{
    protected $rules;

    protected $messages;

    public function passes()
    {


        $this->messages = [
            'email.unique' => 'Email address already exists. Enter a different email.',
            'phone_number.unique' => 'Phone number already exists. Enter a different phone number.',
        ];

        $this->rules = [
            'email' => 'nullable|email',
            'phone_number' => 'nullable|numeric'

        ];

        return parent::passes();
    }
}