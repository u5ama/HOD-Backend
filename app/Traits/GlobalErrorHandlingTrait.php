<?php

namespace App\Traits;

use Illuminate\Support\MessageBag;

trait GlobalErrorHandlingTrait
{
    private $errors = [];

    /**
     * Checking if there is any error occurs
     * @return bool
     */
    public function hasError()
    {
        return count($this->errors) ? true : false;
    }

    /**
     * clears the errors array
     */
    public function resetErrors()
    {
        $this->errors = [];
    }

    /**
     * append current errors array with new error
     * @param $name
     * @param $message
     * @param $code
     */
    public function addError($name, $message,$code='')
    {

        $this->errors[] = [
            'map' => $name,
            'message' => $message,
            'code' => $code,
        ];
    }

    /**
     * append current errors array with new error
     * @param Array $errors
     */
    public function addErrors($errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }


    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * get all errors array
     *
     * @return  MessageBag $errors
     */
    public function getErrorsLaravelFormat()
    {
        $messageBagErrors = new MessageBag();
        foreach ($this->errors as $error) {
            $error = (array)$error;
            $messageBagErrors->add($error['map'], $error['message']);
        }

        return $messageBagErrors;
    }

}