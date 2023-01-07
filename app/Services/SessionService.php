<?php
namespace App\Services;

use Illuminate\Support\Facades\Session;

class SessionService
{
    public function setAuthTokenSession($token)
    {
        Session(['auth_token' => $token]);
    }

    public function getAuthTokenSession()
    {
        return Session('auth_token');
    }

    public function setAuthUserSession($data)
    {
        Session(['user_data' => $data]);
    }

    public function setAdminUserSession($data)
    {
        Session(['admin_data' => $data]);
    }

    public function setOAuthToken($data)
    {
        Session(['social_token' => collect($data)]);
    }

    public function getOAuthToken()
    {
        return Session('social_token');
    }

    public function setGuestEMail($data)
    {
        Session(['scan_guest_email' => $data]);
    }

    public function getScanGuestEmail()
    {
        return Session('scan_guest_email');
    }


//    public function updateUserKeySession($key, $val)
//    {
//        $items = Session::get('user_data', []);
//
//
//        foreach ($items as &$item) {
//            if ($items['dashboard']) {
//                $items['dashboard'] = 12;
//            }
//        }
//
//        Session::set('user_data', collect($items));
//
//
////        Session(
////            [
////                $data = $userData['dashboard'] => 12
////            ]
////        );
//    }

    public function getAuthUserSession()
    {
        return Session('user_data');
    }

    /**
     * get session data of logged in user.
     * @return Session
     */
    public function getAdminUserSession()
    {
        return Session('admin_data');
    }


    public function setUserPaymentSession($data)
    {
        Session(['user_payment_data' => collect($data)]);
    }

    public function getUserPaymentSession()
    {
        return Session('user_payment_data');
    }
}