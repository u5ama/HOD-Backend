<?php

namespace App\Traits;


trait GlobalResponseTrait
{
    /**
     * @param string $message
     * @param $records
     * @param $outcomeCode
     * @param $errorStage
     * @return mixed
     */
    public function helpReturn($message = '', $records = [], $outcomeCode = 200, $errorStage = '')
    {
        $numOfRecords = 0;

        if (is_array($records)) {
            $numOfRecords = count($records);
        } elseif (preg_match('/App/i', get_class($records))) {
            $numOfRecords = 1;
        } elseif (preg_match('/Collection/i', get_class($records))) {
            $numOfRecords = $records->count();
        } elseif (preg_match('/Pagination/i', get_class($records))) {
            $data = ($records->toArray());
            $numOfRecords = count($data['data']);
        }


        $outCome = 'SUCCESS';

        if($outcomeCode != 200)
        {
            $outCome = $this->responseCodeMessages()[$outcomeCode];
            $numOfRecords = 0;
        }

        $response['_metadata'] = [
            'outcome' => $outCome,
            'outcomeCode' => $outcomeCode,
            'numOfRecords' => $numOfRecords,
            'message' => $message,
        ];

        if($errorStage != '')
        {
            $response['_metadata']['errorState'] = $errorStage;
        }

        $response['records'] = $records;

        $response['errors'] = [];
        return $response;
    }

    /**
     * @param $code
     * @param bool $codeInfo
     * @param array $errorsMessage
     * @return mixed
     */
    public function helpError($code, $codeInfo = false, $errorsMessage = [])
    {
        $errors = [
            200 => 'SUCCESS',
            1 => 'SCRIPT_ERROR',
            2 => 'INVALID_PARAMS',
            3 => 'NO_ACCESS',
            4 => 'ALREADY_EXISTS',
            5 => 'INVALID_OR_AUTH_TOKEN_NOT_FOUND',
            6 => 'ALREADY_LOGGED_IN',
            7 => 'TOKEN_EXPIRED',
            8 => "NOT_LOGGED_IN",
            34 => 'DATABASE_ERROR',
            36 => 'LOGIN_FAILED',

            42 => 'RECORD_NOT_UPDATE',

            56 => 'WRONG_VERIFICATION_CODE',
            70 => 'NO_ACCESS_FOR_THIS_ACTION',

            401 => 'UNAUTHORIZED_REQUEST',
            403 => 'Account_unlinked',
            404 => 'RECORD_NOT_FOUND',
            429 => 'REQUEST_LIMITS_EXCEED',
            503 => 'SERVICE_UNAVAILABLE',
            1000 => 'UNKNOWN_ERROR',
        ];

        if (!isset($errors[$code])) {
            $code = 1000;
        }
        $response['_metadata'] = [
            'outcome' => $errors[$code],
            'outcomeCode' => $code,
            'numOfRecords' => 0,
            'message' => $codeInfo,
        ];
        $response['records'] = [];
        $response['errors'] = $errorsMessage;
        return ($response);
    }

    public function responseCodeMessages()
    {
        return [
            200 => 'SUCCESS',
            1 => 'SCRIPT_ERROR',
            2 => 'INVALID_PARAMS',
            3 => 'NO_ACCESS',
            4 => 'ALREADY_EXISTS',
            5 => 'INVALID_OR_AUTH_TOKEN_NOT_FOUND',
            6 => 'ALREADY_LOGGED_IN',
            7 => 'TOKEN_EXPIRED',
            8 => "NOT_LOGGED_IN",
            34 => 'DATABASE_ERROR',
            36 => 'LOGIN_FAILED',

            42 => 'RECORD_NOT_UPDATE',

            56 => 'WRONG_VERIFICATION_CODE',
            70 => 'NO_ACCESS_FOR_THIS_ACTION',

            401 => 'UNAUTHORIZED_REQUEST',
            403 => 'Account_unlinked',
            404 => 'RECORD_NOT_FOUND',
            429 => 'REQUEST_LIMITS_EXCEED',
            503 => 'SERVICE_UNAVAILABLE',
            1000 => 'UNKNOWN_ERROR',
        ];
    }

}