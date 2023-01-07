<?php

namespace App\Services\Validations;

use Illuminate\Validation\Factory;

abstract class LaravelValidator extends AbstractValidator
{

    protected $validator;


    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Pass the data and the rules to the validator
     *
     * @return boolean
     */
    public function passes()
    {
        $validator = $this->validator->make($this->data, $this->rules,$this->messages);

        if( $validator->fails() )
        {
            $this->errors = convert_laravel_input_errors($validator->getMessageBag()->toArray());
            return false;
        }

        return true;
    }

}