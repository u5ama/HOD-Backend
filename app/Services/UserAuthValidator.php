<?php
namespace App\Services;

use App\Traits\GlobalResponseTrait;
use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use Log;

class UserAuthValidator
{
    use GlobalResponseTrait;

    /**
     * This method retrieve the user
     * @param $token
     * @return mixed (200, 401, 1000, 7)
     */
    public function checkUserAuth($token)
    {
        $userData = '';
        try {
            if($token)
            {
                $user = JWTAuth::toUser($token);

                if (!empty($user)) {
                    $userData = User::with
                    (
                        ['userRole' => function ($q) {
                            $q->select('sys_role.id', 'sys_role.name', 'sys_role.slug');
                        }]
                    )->find($user['id']);
                }
            }
            else
            {
                $response = response()->json(
                    [
                        'code' => 401,
                        'error'=>'Token missing.'
                    ]
                );
            }

        } catch (Exception $e) {
            Log::info("UserAuthValidator > " . $e->getMessage());

            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                $response = response()->json(
                    [
                        'code' => 401,
                        'error'=>'Token is Invalid'
                    ]
                );
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $response = response()->json(
                    [
                        'code' => 7,
                        'error'=>'Token is Expired'
                    ]
                );
            }else{
                $response = response()->json
                (
                    [
                        'code' => 1000,
                        'error'=>'Something is wrong'
                    ]
                );
            }
        }

        if($userData)
        {
            return $this->helpReturn('You still visit the site.', $userData);
        }
        else
        {
            if(empty($response)) {
                $response = response()->json(
                    [
                        'code' => 7,
                        'error' => 'Token is Expired.'
                    ]
                );
            }

            return $this->helpError($response->original['code'], $response->original['error']);
        }

    }
}