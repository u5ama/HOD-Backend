<?php

namespace Modules\Admin\Entities;

use App\Entities\AbstractEntity;
use App\Mail\CreateWelcomeRegisterEmail;
use App\Services\SessionService;
use App\Services\Validations\Auth\AuthLoginValidator;
use App\Traits\UserAccess;
use DB;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Log;
use Mail;
use Config;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Models\Business;
use Modules\Business\Models\Industry;
use Modules\Business\Models\Niches;
use Modules\CRM\Models\CrmSettings;
use Modules\User\Models\UserRolesREF;
use Modules\User\Models\User;
use Redirect;
/**
 * Class AuthEntity
 * @package Modules\Auth\Entities
 */
class AdminEntity extends AbstractEntity
{
    use UserAccess;

    protected $loginValidator;

    protected $sessionService;

    /**
     * AuthEntity constructor.
     */
    public function __construct()
    {
        $this->loginValidator = new AuthLoginValidator(resolve('validator'));
        $this->sessionService = new SessionService();
    }

    public function index()
    {
        try
        {
            $list = User::where('id', '!=', 1)->with('business')
                ->whereNull('deleted_at')
                ->get()->toArray();
            return $this->helpReturn('list', $list);
        }
        catch(Exception $e)
        {
            Log::info("admin -> index -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }

    }

    public function deleted()
    {
        try
        {
            $list = User::where('id', '!=', 1)->with('business')
                ->where('deleted_at','!=', '')
                ->get()->toArray();
            return $this->helpReturn('list', $list);
        }
        catch(Exception $e)
        {
            Log::info("admin -> index -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
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

        if(empty($user))
        {
            return $this->helpError(3, "Record not found.");
        }

        $isMatced = Hash::check($password, $user->password);

        $userModified = $user->toArray();

        if($isMatced == 1 && $userModified['user_role'][0]['slug'] == 'admin')
        {

            return $this->helpReturn('You are successfully logged-in', $userModified);
        }

        return $this->helpError(36, "Incorrect email or password.");
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
            $userData = $this->sessionService->getAuthUserSession();

            Log::info("us " . $userData['business'][0]['discovery_status']);

            $userData['discovery_status'] = $request->status;

            $this->sessionService->setAuthUserSession($userData);

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
