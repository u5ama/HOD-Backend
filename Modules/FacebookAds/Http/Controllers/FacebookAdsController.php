<?php

namespace Modules\FacebookAds\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Facades\Socialite;
use Modules\FacebookAds\Entities\FacebookAdsEntity;
use Redirect;
use Log;
use Config;

class FacebookAdsController extends Controller
{
    protected $facebookAdsEntity;

    public function __construct()
    {
        $this->facebookAdsEntity = new FacebookAdsEntity();
    }

    public function redirect()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback(Request $request)
    {
        Log::info("callback" . json_encode($request->all()));
        $requestCheck = $request->all();
        $referType = '';
        $id = '';
        $url = '';
        /**
        Code Change after discussion with Facebook Dev - Keep generic callback and receive business id through session
         */

        Log::info("refe " . $request->session()->get('referType'));
        $webAppDomain = myDomain();
        if($request->session()->get('referType') == 'scan')
        {
            Log::info("if " . $request->session()->get('email'));
            $id = $request->session()->pull('email', 0);
            $referType = $request->session()->pull('referType', null);
        }
        elseif($request->session()->get('referType') == 'home' || $request->session()->get('referType') == 'social_post_settings' || $request->session()->get('referType') == 'weekly_report' || $request->session()->get('referType') == 'posts' || $request->session()->get('referType') == 'posts_demo' || $request->session()->get('referType') == 'get_started')
        {
            Log::info("Else if type  " . $request->session()->get('referType') . " > " . $request->session()->get('business_id'));
            $id = $request->session()->pull('business_id', 0);
            $referType = $request->session()->pull('referType', null);
        }
        elseif($request->session()->get('referType') == 'promotions')
        {
            Log::info("Else promot type  " . $request->session()->get('referType') . " > " . $request->session()->get('business_id'));

            $id = $request->session()->pull('business_id', 0);
            $promotionId = $request->session()->pull('promotion', 0);
            $referType = $request->session()->pull('referType', null);
        }
        else
        {
            Log::info("value of refer " . $request->session()->get('referType'));
            Log::info("Else " . $request->session()->get('business_id'));

            $id = $request->session()->pull('business_id', 0);
        }

        if(!empty($promotionId))
        {
            $response = $this->facebookAdsEntity->getToken($id, $referType, $promotionId);
        }
        else
        {
            $response = $this->facebookAdsEntity->getToken($id, $referType);
        }

        Log::info('check response');
        Log::info($response);
        Log::info('end reponse');
        if (isset($response['_metadata']['outcomeCode']) && $response['_metadata']['outcomeCode'] == 200) {
            Log::info('i am in check');
            $url = $response['records']['url'];
            Log::info("URL " . $url);
            return Redirect::to($url);
        } else {
            Log::info('i am here');
            $webAppDomain = myDomain();
            $url = $webAppDomain .'/'.$request->session()->get('referType');
            Log::info($url);
            return Redirect::to($url);
        }
    }

    public function getLogin(Request $request)
    {
        return $this->facebookAdsEntity->getLogin($request);
    }

    public function getUserAccessToken()
    {
        return $this->facebookAdsEntity->getUserAccessToken();
    }

    /**
     * @param Request $request
     * @return mixed
     * @api {get} /fb-reports/reports-details [ RF-08-01 ] get all facebook ads reports
     * @apiVersion 1.0.0
     * @apiName get all  facebook ads reports
     * @apiParam {String} access_token
     * @apiGroup SocialMedia
     * @apiPermission Secured
     * @apiDescription handle all facebook pages detail on update/add/manualconnect
     */

    public function getAccountReports(Request $request)
    {
        return $this->facebookAdsEntity->getAdsReports($request);
    }

}
