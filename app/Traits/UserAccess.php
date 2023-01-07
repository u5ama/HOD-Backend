<?php

/**
 * Created by Abdul Rehman.
 */

namespace App\Traits;

use App\Services\UserAuthValidator;
use Log;


trait UserAccess
{
    use GlobalResponseTrait;

    private $user;

    private $currentUser;

    private $token;

    protected $value;

    public function superAdminAllow()
    {
        // if we found user response.
        if($this->user['_metadata']['outcomeCode'] == 200)
        {
            // Super Admin
            $user = $this->user['records']->toArray();

            if(!empty($user['user_role'][0]['id']) && $user['user_role'][0]['id'] == 1) {
                return $this->user;
            }
            else
            {
                return $this->helpError(3, 'You\'re not authorized to do this action.');
            }
        }

        return $this->user;
    }

    public function userAllow()
    {
        // if we found user response.
        if($this->user['_metadata']['outcomeCode'] == 200)
        {
            /**
             *  Account Owner (user).
             * currently no user role is defined in user rle for account owner.
             */
            $user = $this->user['records']->toArray();

            if(empty($user['user_role'][0]['id'])) {
                return $this->user;
            }
            else
            {
                return $this->helpError(3, 'You\'re not authorized to do this action.');
            }
        }
        return $this->user;
    }

    public function guestUserAllow()
    {
        // if we found user response.
        if($this->user['_metadata']['outcomeCode'] == 200)
        {
            /**
             *  Guest User
             * currently no user role is defined in user rle for account owner. And make sure
             * this user not be paid user.
             */
            $user = $this->user['records']->toArray();

            if( empty($user['user_role'][0]['id']) && empty($user['amember_id'])) {
                return $this->user;
            }
            else
            {
                return $this->helpError(3, 'You\'re not authorized to do this action. Please check your email. Only Guest user can access this feature.');
            }
        }
        return $this->user;
    }

    public function superSystemAdminAllow()
    {
        // if we found user response.
        if($this->user['_metadata']['outcomeCode'] == 200)
        {
            /**
             *  Account Owner (user).
             * currently no user role is defined in user role for account owner.
             */
            $user = $this->user['records']->toArray();

            if(
                ( !empty($user['user_role'][0]['id']) )
                && ( $user['user_role'][0]['id'] == 1 || $user['user_role'][0]['id'] == 2 )
            ) {
                return $this->user;
            }
            else
            {
                return $this->helpError(3, 'You\'re not authorized to do this action.');
            }
        }

        return $this->user;
    }

    /**
     * @return string
     */
    public function getCurrentUserId()
    {
        $userId = '';

        // if we found user response.
        if($this->user['_metadata']['outcomeCode'] == 200)
        {
            /**
             *  Account Owner (user).
             * currently no user role is defined in user rle for account owner.
             */
            $user = $this->user['records']->toArray();
            $userId = $user['id'];
            return $userId;
        }

        return $userId;
    }

    public function getCurrentUser()
    {
        return $this->user;
    }

    /**
     * authenticate user & return User Object
     *
     * @param $token
     * @return $this
     */
    public function setCurrentUser($token)
    {
        $authUerInfoObj = new UserAuthValidator();
        $this->user = $authUerInfoObj->checkUserAuth($token);
        return $this;
    }

    public function setCurrentUserEmail($request)
    {
        dd("test");
        $authObj = new AuthEntity();

        $this->user = $authObj->retrieveUserInfo($request);

        return $this;
    }

}