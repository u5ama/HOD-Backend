<?php

namespace App\Services\Validations\Auth;

use App\Services\Validations\LaravelValidator;

class AuthLoginValidator extends LaravelValidator
{

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];


}
