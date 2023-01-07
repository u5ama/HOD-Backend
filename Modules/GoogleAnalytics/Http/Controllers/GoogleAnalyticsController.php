<?php

namespace Modules\GoogleAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Entities\BusinessEntity;
use Modules\GoogleAnalytics\Entities\GoogleAnalyticsEntity;
use Redirect;

class GoogleAnalyticsController extends Controller
{
    protected $businessEntity;

    protected $googleAnalyticsEntity;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();

        $this->googleAnalyticsEntity = new GoogleAnalyticsEntity();

    }

    public function getLogin(Request $request)
    {
        $url =  $this->googleAnalyticsEntity->getLogin($request);
        return response()->json(compact('url'));
    }

    public function callback(Request $request)
    {
        $url = $this->googleAnalyticsEntity->getAccessToken($request);
        return Redirect::to($url);
    }
    /**
     * @api {get} /google-analytics/get-accounts [ RF-15-01 ] Get Account From Google Analytics
     * @apiVersion 1.0.0
     * @apiName Get Account From Google Analytics
     * @apiGroup Googe Analytics
     * @apiParam {String} token
     * @apiParam {String} refresh_token
     * @apiPermission Secured
     * @apiDescription Get Account from google analytics by pass refresh token or token(local).
     */
    public function getAccounts(Request $request)
    {
        $accounts =  $this->googleAnalyticsEntity->getAccounts($request);
        return response()->json(compact('accounts'));
    }
    /**
     * @api {get} /google-analytics/get-web-property [ RF-15-02 ] Get Web Property
     * @apiVersion 1.0.0
     * @apiName Get Web Property
     * @apiGroup Googe Analytics
     * @apiParam {Number} account_id
     * @apiParam {String} token
     * @apiParam {String} refresh_token
     * @apiPermission Secured
     * @apiDescription Get Web Property using Account id and refresh token.
     */
    public function getWebProperties(Request $request)
    {
        $accounts =  $this->googleAnalyticsEntity->getWebProperties($request);
        return response()->json(compact('accounts'));
    }
    /**
     * @api {get} /google-analytics/get-profile-view [ RF-15-03 ] Get Profile View
     * @apiVersion 1.0.0
     * @apiName Get Profile View
     * @apiGroup Googe Analytics
     * @apiParam {Number} view_id
     * @apiParam {String} token
     * @apiParam {String} refresh_token
     * @apiParam {String} name
     * @apiParam {String} website
     * @apiPermission Secured
     * @apiDescription Get Profile Views by using view id and other params.
     */
    public function getProfileViews(Request $request)
    {
        return $this->googleAnalyticsEntity->getProfileViews($request);

    }

    public function exchangeRefreshToken(Request $request)
    {
        return $this->googleAnalyticsEntity->exchangeRefreshToken($request);
    }

    /**
     * @api {get} /google-analytics/get-profile-view-cron-job [ RF-15-04 ] Get Profile View Cron Job
     * @apiVersion 1.0.0
     * @apiName Get Profile View Cron Job
     * @apiPermission Secured
     * @apiDescription Get Profile View Cron Job
     */
    public function getProfileViewsCronJob(Request $request)
    {
        return $this->googleAnalyticsEntity->getProfileViewsCronJob($request);
    }

}
