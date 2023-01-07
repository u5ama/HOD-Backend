<?php

namespace Modules\CRM\Services\Validations\Reviews;

use App\Services\Validations\LaravelValidator;

class AddReviewValidator extends LaravelValidator
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

            'email' => 'nullable|email|max:40',
            'phone_number' => 'nullable|numeric',
            'first_name' => 'max:40',
            'last_name' => 'max:40',

        ];

        return parent::passes();
    }
}