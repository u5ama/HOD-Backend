<?php

namespace Modules\Business\Entities;

use App\Entities\AbstractEntity;
use App\Services\SessionService;
use App\Traits\UserAccess;
use App\User;
use DB;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\URL;
use Log;
use Mail;
use Config;
use Modules\Business\Models\Business;
use Modules\Business\Models\Industry;
use Modules\Business\Models\Niches;
use Modules\Business\Models\SocialProfile;
use Modules\ThirdParty\Entities\FacebookEntity;
use Modules\ThirdParty\Entities\GooglePlaceEntity;
use Modules\ThirdParty\Entities\SocialEntity;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\TripAdvisorEntity;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Modules\ThirdParty\Models\TripadvisorMaster;
use Modules\User\Models\UserRolesREF;
use Modules\ThirdParty\Entities\YelpEntity;
use Redirect;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthEntity
 * @package Modules\Auth\Entities
 */
class BusinessEntity extends AbstractEntity
{
    use UserAccess;

    protected $tripAdvisor;
    protected $loginValidator;

    protected $googlePlaces;

    protected $facebook;

    protected $sessionService;

    protected $socialEntity;

    protected $thirdPartyEntity;

    public function __construct()
    {
        $this->tripAdvisor = new TripAdvisorEntity();
        $this->googlePlaces = new GooglePlaceEntity();
        $this->socialEntity = new SocialEntity();
        $this->thirdPartyEntity = new ThirdPartyEntity();
    }

    public function userSelectedBusiness($request)
    {
        try {
            if (!empty($request->input('token'))){
                JWTAuth::setToken($request->input('token'));
                $userData = JWTAuth::toUser();
            }else{
                $userData['id'] = $request->user_id;
            }

            $full = '';
            if(!empty($userData)) {
                if($full == '')
                {
                    $userBusiness = Business::with([
                        'niche' => function ($q) {
                            $q->with('industry');
                        }
                    ])->where('user_id', $userData['id'])->first();
                }
                else
                {
                    $userBusiness = Business::with('country')->with([
                        'niche' => function ($q) {
                            $q->with('industry');
                        }
                    ])->where('user_id', $userData['id'])->first();
                }
            }

            if (!empty($userBusiness)) {
                $userBusiness = $userBusiness->toArray();
                return $this->helpReturn("Business Result.", $userBusiness);
            } else {
                return $this->helpError(404, ' No Business found in our system.');
            }
        } catch (Exception $exception) {
            Log::info(" userSelectedBusiness > " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function businessProfileUpdate($request)
    {
        try
        {
            $businessResult = $this->userSelectedBusiness($request);

            if($businessResult['_metadata']['outcomeCode'] != 200)
            {
                return $businessResult;
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];

            $data = $request->except('send', 'attach_logo', 'attach_avatar', 'data', 'token');

            if ($request->hasFile('attach_avatar')) {
                $attachedFile = $request->attach_avatar;
                $i = 0;

                foreach ($attachedFile as $file) {

                    $file = $attachedFile[$i];
                    $extension = $file->getClientOriginalExtension();

                    $file_size = $file->getSize();
                    $file_size = number_format($file_size / 1048576, 2);

                    $avatarName = 'avatar' . time() . '.' . $extension;

                    Storage::disk('local')->put($avatarName, File::get($file));

                    $url = Storage::url($avatarName);
                }

                if(!empty($avatarName))
                {
                    $data['avatar'] = $avatarName;
                }
            }
            if ($request->hasFile('attach_logo')) {
                $attachedFile = $request->attach_logo;

//               dd($attachedFile);
//                foreach ($attachedFile as $file) {
                    $file = $attachedFile;
                    $extension = $file->getClientOriginalExtension();

                    $file_size = $file->getSize();
                    $file_size = number_format($file_size / 1048576, 2);

                    $logoName = 'logo' . time() . '.' . $extension;

                    Storage::disk('local')->put($logoName, File::get($file));
                    $logoUrl = Storage::url($logoName);

//                }

                if(!empty($logoName))
                {
                    $data['logo'] = URL::asset('storage/app/').'/'.$logoName;
                }
            }
            Business::where('business_id', $businessId)
                ->update($data);

            $businessData = Business::where('business_id', $businessId)->get()->toArray();

            return $this->helpReturn('Your profile updated.', $businessData);
        }
        catch(Exception $e)
        {
            Log::info("businessProfileUpdate -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function socialProfileUpdate($request)
    {
        try
        {
            $businessResult = $this->userSelectedBusiness();

            if($businessResult['_metadata']['outcomeCode'] != 200)
            {
                return $businessResult;
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];

            $data = $request->except('send');

            SocialProfile::updateorCreate(
                ['business_id' => $businessId],
                $data
            );

            return $this->helpReturn('Social profile updated.');
        }
        catch(Exception $e)
        {
            Log::info("businessProfileUpdate -> " . $e->getMessage() . ' > ' . $e->getLine() . ' > ' . $e->getCode());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function registerBusiness($request)
    {
        $result = Business::create([
            'user_id' => $request->get('user_id'),
            'business_name' => $request->get('business_name'),
            'business_location' => $request->get('business_location'),
            'phone' => $request->get('phone'),
            'website' => $request->get('website'),
            'address' => $request->get('address'),
            'business_status' => $request->get('business_status'),
            'discovery_status' => 3,
            'state' => $request->get('state'),
            'city' => $request->get('city'),
            'country' => $request->get('country'),
            'zip_code' => $request->get('zip_code'),
            'user_agent' => $request->get('user_agent'),
            'targetUrl' => $request->get('businessUrl'),
        ]);
        if(!empty($result['business_id']))
        {
            return $this->helpReturn('Business Registered', $result);
        }
        return $this->helpError(1, 'Some Problem happened.');
    }

    public function collectBusinessData($request)
    {
        Log::info("saa");
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        if(!empty($userData))
        {
            $businessData = Business::where('user_id', $userData['id'])->first();

            Log::info("NEXT " . json_encode($businessData));
            if(!empty($businessData))
            {
                $requestAppend = [
                    'userID' => $userData['id'],
                    'business_id' => $businessData['business_id'],
                    'name' => $businessData['business_name'],
                    'businessKeyword' => $businessData['business_name'],
                    'business_address' => $businessData['address'],
                    'phone' => $businessData['phone'],
                    //'targetUrl' => 'https://maps.google.com/?cid=14315959238430444407'
                    'targetUrl' => $businessData['targetUrl']
                ];
                $request->merge($requestAppend);

                $result = $this->thirdPartyConnect($request);

                if($result['_metadata']['outcomeCode'] == 200)
                {
                    $businessData->update(
                        [
                            'discovery_status' => 5
                        ]
                    );

                    // change status in db.
                    Log::info("done ");
                    return $this->helpReturn('Business Registered');
                }
            }
        }

        return $this->helpError(1, 'Some Problem happened. please try again.');
    }

    public function thirdPartyConnect($request)
    {
        try {
            Log::info("Ready");

            $type = !empty($request->type) ? $request->type : 'all';

            if(empty($request->get('business_id')) || empty($request->get('userID')))
            {
                $businessResult = $this->userSelectedBusiness($request);
                if ($businessResult['_metadata']['outcomeCode'] != 200) {
                    return $this->helpError(1, 'Problem in selection of your business.');
                }

                $websiteEntity = new WebsiteEntity();
                $web = $websiteEntity->websiteRecord($businessResult);

                Log::info(" i am here");
                Log::info($web);

                $businessResult = $businessResult['records'];
                $requestAppended = [
                    'business_id' => $businessResult['business_id'],
                    'userID' => $businessResult['user_id'],
                ];
                $request->merge($requestAppended);
            }

            $response = '';
            $type = strtolower($type);
            if ($type == 'googleplaces' || $type == 'all') {
                $response = $this->googlePlaces->updateGooglePlacesMaster($request);
            }

            /*
            if ($type == 'facebook' || $type == 'all') {
                $response = $this->facebook->updateThirdPartyMaster($request);
            }
           */

            if($type == 'all')
            {
                return $this->helpReturn('Process completed');
            }

            return $response;
        }
        catch(Exception $e)
        {
            Log::info("BusinessEntity > thirdPartyConnect >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function businessDirectoryList($request, $requestedUser = 'user')
    {
        try {
            $businessResult = $this->userSelectedBusiness();

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $userBusiness = $businessResult['records'];
            $businessId = $userBusiness['business_id'];

            $thirdObj = new TripadvisorMaster();
            $socialMasterObj = new SocialMediaMaster();

            $requestAppended = [
                'business_id' => $businessId,
            ];
            $request->merge($requestAppended);

            if ($requestedUser != 'guest') {
                $this->socialEntity->socialModuleUpdate($request);
            }

            // get business issues
            $businessIssues = $thirdObj->businessApiResponse($businessId);

            $SocialApiIssues = $socialMasterObj->SocialApiResponse($businessId, 'Facebook');

            if ($SocialApiIssues) {
                $businessIssues = array_merge($businessIssues->toArray(), $SocialApiIssues->toArray());
            }

            $businessIssues = json_decode(json_encode($businessIssues), true);

            $businessRecord['userBusiness'] = $userBusiness;
            $businessIssues = $this->businessIssuesSorting($businessIssues, 'directory');

            $data = [];
            foreach ($businessIssues as $issueData) {
                $data[] = $issueData;
            }
            $businessRecord['businessIssues'] = $data;
            return $this->helpReturn("Business Result.", $businessRecord);
        } catch (Exception $exception) {
            Log::info(" businessDirectoryList > " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * @param $businessIssues
     * @param string $module (all, directory)
     * @return array
     */
    public function businessIssuesSorting($businessIssues, $module = 'all')
    {
        try {
            $issueData = [];

            $i = 0;
            $totalIssues = 0;
            $moduleSites = moduleSiteList();

            foreach ($businessIssues as $index => $businessIssue) {
                $issuesFound = 0;

                $currentCounterType = str_replace(' ', '', strtolower($businessIssue['type']));

                $name = $businessIssue['name'];
                $issueId = $businessIssue['issue_id'];
                $type = $businessIssue['type'];

                if ($module == 'all') {
                    /**
                     * 1)
                     * module sites deleted if this matched from
                     * business issue type
                     * like if tripadvisor == tripadvisor delete it
                     * so which module not matched with any issue type that will be left
                     * so we handle that unmatched module later in this code.
                     */
                    foreach ($moduleSites as $key => $site) {
                        $currentSite = str_replace(' ', '', strtolower($site));
                        if ($currentCounterType == $currentSite) {
                            unset($moduleSites[$key]);
                            break;
                        }
                    }
                }

                if ($name == '' && $issueId == '') {
                    if ($module == 'all') {
                        $issueData[$i]['type'] = $type;
                        $issueData[$i]['message'] = 'Not Setup';
                    } else {
                        $issueData[$i] = $businessIssue;
                        $issueData[$i]['issueList'] = [];
                    }
                }
                else {
//                    Log::info("Not empty");
//                    Log::info($businessIssue);
                    $matched = false;

//                    Log::info("issueData");
//                    Log::info($issueData);

                    if (!empty($issueData)) {
                        foreach ($issueData as $issueIndex => $issueRecord) {
                            $currentIssueType = str_replace(' ', '', strtolower($issueRecord['type']));
                            if ($currentCounterType == $currentIssueType) {
                                $matched = true;
                                $issuesFound = ( !empty($issueData[$issueIndex]['issuesFound']) ) ? $issueData[$issueIndex]['issuesFound'] : 0;
                                $issuesFound++;
                                $totalIssues++;
                                $issueData[$issueIndex]['issueList'][] = $businessIssue['title'];
                                $issueData[$issueIndex]['issuesFound'] = $issuesFound;
                                break;
                            }
                        }
                    }

                    if ($matched == '') {
                        if ($module == 'all') {
                            $issueData[$i]['type'] = $businessIssue['type'];
                        } else {
                            $issueData[$i] = $businessIssue;
                        }

                        if ($businessIssue['issue_id'] != '') {
                            $issueData[$i]['issueList'][] = $businessIssue['title'];
                            $issuesFound++;
                            $totalIssues++;
                        } else {
                            $issueData[$i]['issueList'] = [];
                        }

                        $issueData[$i]['issuesFound'] = $issuesFound;
                    }
                }

                $i++;
            }

            if ($module == 'all') {
                /**
                 * 2)
                 * which module is left from Procedure-1
                 * we'll handling this here
                 * so we can tell this page is not setup yet.
                 */
                foreach ($moduleSites as $site) {
                    $issueData[$i]['type'] = $site;
                    $issueData[$i]['message'] = 'Not Setup';
                    $i++;
                }

                $issueData['totalIssues'] = $totalIssues;
            }

            return $issueData;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }
}
