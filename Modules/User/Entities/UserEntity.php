<?php

namespace Modules\User\Entities;

use App\Entities\AbstractEntity;
use App\Mail\SendInvitation;
use App\Services\SessionService;
use App\Traits\UserAccess;
use Illuminate\Support\Facades\Mail;
use Modules\Business\Entities\WebsiteEntity;
use Modules\Business\Models\Website;
use Modules\User\Emails\InvitationEmail;
use Modules\User\Emails\ResetPasswordMail;
use Modules\User\Models\User;
use DB;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Log;
use Config;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Models\Business;
use Modules\Business\Models\Industry;
use Modules\Business\Models\Niches;
use Modules\CRM\Entities\CRMEntity;
use Modules\CRM\Models\CrmSettings;
use Modules\CRM\Models\Recipient;
use Modules\User\Models\UserRolesREF;
use Modules\User\Models\Users;
use Modules\User\Services\Validations\Auth\AuthLoginValidator;
use Redirect;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthEntity
 * @package Modules\Auth\Entities
 */
class UserEntity extends AbstractEntity
{
    use UserAccess;

    protected $loginValidator;
    protected $sessionService;
    /**
     * AuthEntity constructor.
     */
    public function __construct()
    {
        $this->sessionService = new SessionService();
    }


    public function register($request)
    {
        try
        {
            $user = User::where('email', $request->get('email'))->first();
            if(!empty($user))
            {
                return $this->helpError(4, 'This email is already exist. Change your email or logged in from this email.');
            }

            return DB::transaction(function () use ($user, $request)
            {
                $userResult = User::create([
                    'first_name' => $request->get('firstname'),
                    'last_name' => $request->get('lastname'),
                    'email' => $request->get('email'),
                    'password' => Hash::make($request->get('password')),
                ]);

                $token = JWTAuth::fromUser($userResult);
                $userResult['token'] = $token;
                Log::info("user Result " . json_encode($userResult));

                $userID = $userResult['id'];

                Log::info("userID $userID");

                    $UserRolesREF = UserRolesREF::create(
                        [
                            'user_id' => $userID,
                            'role_id' => 2
                        ]
                    );
                    Log::info("REF Result " . json_encode($UserRolesREF));

                    $businessAccess = new BusinessEntity();

                    $requestAppend = [
                        'user_id' => $userID,
                    ];

                    $request->merge($requestAppend);
                    $result = $businessAccess->registerBusiness($request);
                    Log::info("B Result " . json_encode($result));

                    $websiteEntity = new WebsiteEntity();
                    $web = $websiteEntity->websiteRecord($result);

                    Log::info(" i am here");
                    Log::info($web);
                try
                {
                    Mail::to($request->email)->send(new SendInvitation($request->firstname, $request->email));
                }
                catch(Exception $e)
                {
                    Log::info("mail failure -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
                }

                    return $userResult;
            });
        }
        catch(Exception $e)
        {
            Log::info("register -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function login($request)
    {
        $data = $request->all();

        if (!$this->loginValidator->with($data)->passes()) {
            return $this->helpError(2, "Fields are required to login.", $this->loginValidator->errors());
        }

        $email = $request->email;
        $password = $request->password;

        $user = User::with('userRole')->where('email', $email)
                ->first();

        Log::info("user");
        Log::info($user);

        if(empty($user))
        {
            return $this->helpError(3, "Record not found.");
        }

        $isMatced = Hash::check($password, $user->password);

        $userModified = $user->toArray();

        if($isMatced == 1 && $userModified['user_role'][0]['slug'] == 'user')
        {

            if($user['account_status'] == 'deleted')
            {
                return $this->helpError(403, "Your account has been deleted. Please contact support");
            }

            $userBusiness = $user->business;

            $phone = '';
            if(!empty($userBusiness))
            {
                $user['business'] = $userBusiness;
                $user['discovery_status'] = $userBusiness[0]['discovery_status'];
                $phone = $userBusiness[0]['phone'];
            }

            $this->sessionService->setAuthUserSession($user->toArray());
            $userID = $user['id'];

            $crmsetting = CrmSettings::where('user_id', $userID)->first();

            if( empty($crmsetting) ) {
                $crmSettingResult = CrmSettings::create( [
                    'user_id' => $userID,
                    'enable_get_reviews' => 'Yes',
                    'smart_routing' => 'Enable',
                    'sending_option' => '1'
                ]);

                if(!empty($crmSettingResult['id']))
                {

                }
            }

            return $this->helpReturn('You are successfully logged-in');
        }

        return $this->helpError(36, "Incorrect email or password.");
    }

    public function forgetPassword($request)
    {
        $email = $request->get('email');
        $user = User::where('email', $email)->first();
        $first_name = $user->first_name;
        $last_name = $user->last_name;
        $link = 'sample';
        Mail::to($email)->send(new ResetPasswordMail($first_name, $last_name,$link));
        return $this->helpReturn('Mail Send Successfully');
    }
    public function userProfileUpdate($request)
    {
        try
        {
            $user = User::where('email', $request->get('email'))->first();

            if(empty($user))
            {
                return $this->helpError(404, 'No record exist.');
            }

            return DB::transaction(function () use ($user, $request)
            {
                $data = $request->all();

                $user->update($data);

                $userID = $user['id'];
                Business::where('user_id', $userID)
                    ->update(
                        ['phone' => $data['phone']]
                    );

                return $this->helpReturn('Your profile updated.');
            });
        }
        catch(Exception $e)
        {
            Log::info("register -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }
    public function deleteUserAccount($request)
    {
        try
        {
            $user = User::where('id', $request->get('id'))->first();

            if(empty($user))
            {
                return $this->helpError(404, 'No record exist.');
            }

            Log::info("yes next");

            User::where('id', $request->get('id'))->delete();

            return $this->helpReturn('Account has been deleted.');
        }
        catch(Exception $e)
        {
            Log::info("deleteUserAccount -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function changeUserAccountStatus($request)
    {
        try
        {
            $user = User::where('email', $request->get('email'))->first();

            if(empty($user))
            {
                return $this->helpError(404, 'No record exist.');
            }

            $status = ($request->get('status') == 'deleted') ? 'deleted' : '';
            $deleteBy = (!empty($request->get('delete_by'))) ? $request->get('delete_by') : 0;

            $data['account_status'] = $status;

            Log::info("data");
            Log::info($data);

            User::where('email', $request->get('email'))->update(
                [
                    'account_status' => $status,
                    'delete_by' => $deleteBy
                ]
            );

            return $this->helpReturn('User profile updated.', $user);
        }
        catch(Exception $e)
        {
            Log::info("deactivateUserAccount -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function superLogin($request)
    {
        try
        {
            $email = $request->email;

            $user = User::with('userRole')->where('email', $email)
                ->first();

            Log::info("user");
            Log::info($user);

            if(empty($user))
            {
                return $this->helpError(3, "Record not found.");
            }

            $isMatced = 1;

            $userModified = $user->toArray();

            if($isMatced == 1 && $userModified['user_role'][0]['slug'] == 'user')
            {
                $userBusiness = $user->business;

                $phone = '';
                if(!empty($userBusiness))
                {
                    $user['business'] = $userBusiness;
                    $user['discovery_status'] = $userBusiness[0]['discovery_status'];
                    $phone = $userBusiness[0]['phone'];
                }

                //$this->sessionService->setAuthUserSession($user->toArray());
                $userID = $user['id'];

                $crmsetting = CrmSettings::where('user_id', $userID)->first();

                if( empty($crmsetting) ) {
                    $crmSettingResult = CrmSettings::create( [
                        'user_id' => $userID,
                        'enable_get_reviews' => 'Yes',
                        'smart_routing' => 'Enable',
                        'sending_option' => '1'
                    ]);
                }

                $token = JWTAuth::fromUser($user);
                $request->request->add(['token' => $token]);

                $crmsetting = CrmSettings::where('user_id', $user['id'])->first();

                if( empty($crmsetting) ) {
                    $crmSettingResult = CrmSettings::create( [
                        'user_id' => $user['id'],
                        'enable_get_reviews' => 'Yes',
                        'smart_routing' => 'Enable',
                        'sending_option' => '1'
                    ]);

                    if(!empty($crmSettingResult['id']))
                    {

                    }
                }

                $business = Business::where('user_id', $user['id'])->first();
                $myData['userData']['business_id'] = $business->business_id;
                $myData['userData']['business_name'] = $business->business_name;
                $myData['userData']['business_website'] = $business->website;
                $myData['token'] = $token;

                return $this->helpReturn('You are successfully logged-in', $myData);
            }

            return $this->helpError(36, "Unable to login in this account.");
        }catch(Exception $e)
        {
            Log::info("superlogin -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function deactivateUserAccount($request)
    {
        try
        {
            $user = User::where('email', $request->get('email'))->first();

            if(empty($user))
            {
                return $this->helpError(404, 'No record exist.');
            }

            $data['account_status'] = 'deleted';
            $data['leaving_subject'] = $request->leavingTitle;
            $data['leaving_note'] = $request->leavingNote;

            Log::info("data");
            Log::info($data);

            User::where('email', $request->get('email'))->update(
                [
                    'account_status' => 'deleted',
                    'leaving_subject' => $request->get('leavingTitle'),
                    'leaving_note' => $request->get('leavingNote')
                ]
            );

            return $this->helpReturn('Your profile updated.', $user);
        }
        catch(Exception $e)
        {
            Log::info("deactivateUserAccount -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function updateSession($request)
    {
        if(!empty($request->status))
        {
            JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();

            Log::info("us " . $userData['business'][0]['discovery_status']);

            $userData['discovery_status'] = $request->status;

            if(!empty($userData) && $request->status == 6) {
                $businessData = Business::where('user_id', $userData['id'])->first();

                if(!empty($businessData))
                {
                    $businessData->update(
                        [
                            'discovery_status' => 1
                        ]
                    );
                }
            }
        }

        return $this->helpReturn('Process done.');
    }
}
