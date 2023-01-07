<?php

namespace Modules\User\Http\Controllers;

use App\Mail\SendResetPassword;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Modules\Business\Models\Business;
use Modules\CRM\Models\CrmSettings;
use Modules\User\Emails\ResetPasswordMail;
use Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Modules\User\Entities\UserEntity;
use Modules\Business\Entities\BusinessEntity;
use Log;
use Config;
class UserController extends Controller
{
    protected $userEntity;

    protected $businessEntity;

    /**
     * AuthEntity constructor.
     */
    public function __construct()
    {
       $this->businessEntity = new BusinessEntity();
       $this->userEntity = new UserEntity();
    }

    public function authenticate(Request $request)
    {
        if (!$request->get('token')) {

            $user = User::where(['email' => $request->get('email'), 'account_status' => 'deleted'])->where('id', '!=', 1)->first();
            if ($user){
                return response()->json(['error' => 'Account is deactivated'], 402);
            }else{
                $credentials = $request->only('email', 'password');
            }

            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'Invalid email or password'], 400);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
        }else{
            $token = $request->get('token');
        }
        JWTAuth::setToken($token);
        $userData = JWTAuth::toUser();

        $role = $request->get('role');

        if ($role != 'admin'){
            Log::info(' ia here');
            Log::info($userData['id']);
            $currentDate = Carbon::now();
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('m/d/yy');

            if ($userData['user_trial'] == $formatedDate){
                return response()->json(['error' => 'Error in subscription'], 401);
            }
        }

        $crmsetting = CrmSettings::where('user_id', $userData['id'])->first();

        if( empty($crmsetting) ) {
            $crmSettingResult = CrmSettings::create( [
                'user_id' => $userData['id'],
                'enable_get_reviews' => 'Yes',
                'smart_routing' => 'Enable',
                'sending_option' => '1'
            ]);

            if(!empty($crmSettingResult['id']))
            {

            }
        }
        $business = Business::where('user_id', $userData['id'])->first();
        $userData['business_id'] = $business->business_id;
        $userData['business_name'] = $business->business_name;
        $userData['business_website'] = $business->website;

        return response()->json(compact('token', 'userData'));
    }

    public function register(Request $request)
    {
        $user = User::where(['email' => $request->get('email'), 'account_status' => 'deleted'])->first();
        if (!empty($user)){
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:users',
            ]);
            return response()->json($validator->errors()->toJson(), 401);
        }else{
            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }
        }

        $result = $this->userEntity->register($request);
        if (!empty($result)){
            $token = $result->token;
        }

        JWTAuth::setToken($token);
        $userData = JWTAuth::toUser();
        $business = Business::where('user_id', $userData['id'])->first();
        $userData['business_id'] = $business->business_id;
        $userData['business_name'] = $business->business_name;
        $userData['business_website'] = $business->website;

        $crmsetting = CrmSettings::where('user_id', $userData['id'])->first();

        if( empty($crmsetting) ) {
            $crmSettingResult = CrmSettings::create( [
                'user_id' => $userData['id'],
                'enable_get_reviews' => 'Yes',
                'smart_routing' => 'Enable',
                'sending_option' => '1'
            ]);

            if(!empty($crmSettingResult['id']))
            {

            }
        }
        return response()->json(compact('token', 'userData'),200);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    public function updateTrial(Request $request){

        $user_id = $request->get('id');
        $endingDate = $request->get('endValue');
        if (!empty($endingDate)){
             User::where('id', $user_id)->update([
                'user_trial' => $endingDate
            ]);
            return response()->json(['success' => 'true', 'message' => 'Trial Date added'],200);
        }else{
            return response()->json(['success' => 'false', 'message' => 'Add trial Date'], 200);
        }

    }

    public function endTrial(Request $request){

        $user_id = $request->get('id');
        if (!empty($user_id)){
             User::where('id', $user_id)->update([
                'user_trial' => null
            ]);
            return response()->json(['success' => 'true', 'message' => 'Trial Ended'],200);
        }else{
            return response()->json(['success' => 'false', 'message' => 'Issue Found'], 200);
        }

    }

    public function forgotpassword(Request $request)
    {
        try {
            $email = $request->get('email');
            $user = User::where('email', $email)->first();
            $first_name = 'sample';
            $last_name = 'sample';
            $link = 'sample';
            Mail::to($email)->send(new SendResetPassword($first_name, $last_name, $link));

            $success = true;
            $message = "Email Link sent to User";
            return response()->json(compact('success', 'message'), 200);

        } catch (\Exception $e) {
            Log::info("internal saveFeedback");
            print_r($e->getMessage());
            /*$success = false;
            $message = "send User's email is Failed!";
            return response()->json(compact('success', 'message'), 200);*/
        }

    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        $match_current_password = User::where(["id"=>$request->user_id,'plane_password'=>$request->current_password])->count();

        if($match_current_password>0)
        {
            $query = User::where('id',$request->user_id)->update(['password'=> Hash::make($request->get('password')),'plane_password'=> $request->get('password') ]);

            if($query)
            {
                return response()->json(['success' => 'true'],200);

            }else{
                return response()->json(['error' => 'Something Went Wrong'], 400);
            }
        }else{
            return response()->json(['error' => 'Current password did not match.'], 400);
        }
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['success'=>'true'],200);
    }
}
