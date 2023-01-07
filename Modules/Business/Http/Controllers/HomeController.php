<?php

namespace Modules\Business\Http\Controllers;

use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Entities\WebsiteEntity;
use Modules\Business\Models\Business;
use Modules\CRM\Entities\CRMEntity;
use Modules\ThirdParty\Entities\DashboardWidgetEntity;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Tymon\JWTAuth\Facades\JWTAuth;
use Log;

class HomeController extends Controller
{
    protected $data;
    protected $crmEntity;

    protected $sessionService;

    protected $thirdPartyEntity;

    protected $dashboardWidgetEntity;

    public function __construct()
    {
        $this->crmEntity = new CRMEntity();
        $this->sessionService = new SessionService();
        $this->thirdPartyEntity = new ThirdPartyEntity();
        $this->dashboardWidgetEntity = new DashboardWidgetEntity();
    }

    public function home(Request $request)
    {
        $ob = new BusinessEntity();

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $business = Business::where('user_id', $userData['id'])->first();
        $userData['business_id'] = $business->business_id;
        $userData['business_name'] = $business->business_name;

        Log::info("user data >");
        Log::info($userData);
        $this->data['userData'] = $userData;

        $this->data['businessData'] = '';
        $this->data['scanResult'] = '';
        if ($userData['discovery_status'] == 1 || $userData['discovery_status'] == 6) {
            $businessData = $ob->businessDirectoryList($request);

            $businessResult = $businessData['records']['userBusiness'];
            $this->data['scanResult'] = $businessData['records']['businessIssues'];
            $this->data['businessResult'] = $businessResult;

            if (!empty($businessResult['website'])) {
                $webObj = new WebsiteEntity();

                $webResult = $webObj->trackWebsiteStatus($request, true);

                if ($webResult['_metadata']['outcomeCode'] == 200) {
                    $this->data['webResult'] = $webResult['records'];
                }
            }

            $socialResult = SocialMediaMaster::where('business_id', $businessResult['business_id'])->orderBy('type')->get()->toArray();


            if (!empty($socialResult)) {
                $this->data['socialResult'] = $socialResult[0];
            }

            $this->data['twitterResult'] = (!empty($socialResult[1]) && strtolower($socialResult[1]['type']) == 'twitter') ? $socialResult[1] : '';
        }
        // $this->data['lastReviews'] = $this->dashboardWidgetEntity->lastReviews($userData);
        $this->data['overAllRating'] = $this->dashboardWidgetEntity->overAllRating($userData);
        $this->data['publicReviews'] = $this->dashboardWidgetEntity->publicReviews($userData);
        $this->data['totalPercentage'] = $this->dashboardWidgetEntity->reviewsGrowth($userData);

        $this->data['sentiments'] = $this->dashboardWidgetEntity->sentiments($userData);
        $data = $this->data;
        return response()->json($data, 200);
    }
}
