<?php

namespace Modules\GoogleAdwords\Entities;

use App\Entities\AbstractEntity;
use Carbon\Carbon;
use Exception;
use Google\AdsApi\AdWords\v201809\cm\AdGroupAdService;
use Google\AdsApi\AdWords\v201809\cm\AdGroupService;
use Google\AdsApi\AdWords\v201809\cm\CampaignService;
use Google_Client;
use Google_Service_Analytics;
use Google_Service_Analytics_AdWordsAccount;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_ReportRequest;
use Log;
use JWTAuth;
use DB;
use Config;
use Edujugon\GoogleAds\GoogleAds;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Models\Website;
use Modules\GoogleAdwords\Models\GoogleAdwordsMaster;
use Modules\GoogleAdwords\Models\GoogleAdwordsStats;
use Modules\GoogleAnalytics\Entities\GoogleAnalyticsEntity;
use Modules\ThirdParty\Models\StatTracking;


class GoogleAdwordEntitiy extends AbstractEntity
{
    protected $googleAnalyticsEntity;

    protected $data = [];
    public function __construct()
    {
        $this->googleAnalyticsEntity = new GoogleAnalyticsEntity();
    }

    function getLogin($request)
    {
        Log::info('google Ads testing');
        Log::info($request);
        $url =  'https://accounts.google.com/o/oauth2/auth?client_id=387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com&redirect_uri=' . config::get('apikeys.myCallBack') . '&scope=https://www.googleapis.com/auth/analytics&response_type=code&approval_prompt=force&access_type=offline';
        Log::info('google Ads url');
        Log::info($url);
        return $url;
    }

    function getAccessToken($request){

        Log::info($request);

        try
        {
            $ads = new GoogleAds();

            $webAppDomain = myDomain();

            Log::info("link > ".public_path());

            $client = new Google_Client();
            $client->setAuthConfig(public_path('client_secret_387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com.json'));

            $client->setApplicationName("Hello Analytics Reporting");

            if(isset($request['error']))
            {
                $url = $webAppDomain;
                return $url;
            }

            $client->addScope('https://www.googleapis.com/auth/analytics.manage.users');
            $client->setIncludeGrantedScopes(true);   // incremental auth

            $client->setRedirectUri(config::get('apikeys.myCallBack'));

            $client->setApprovalPrompt('consent');

            $client->fetchAccessTokenWithAuthCode($request['code']);

            $webAppDomain = myDomain();

            $url = $webAppDomain;

            Log::info($url);
            Log::info("i am here");

            $refresh_token = $client->getAccessToken()['refresh_token'];

            Log::info($refresh_token);
            Log::info(" token");

            $ads->oAuth([
                'refreshToken' => $refresh_token
            ]);

            $refresh_token = urlencode($refresh_token);
            Log::info($refresh_token);

            if (!empty($refresh_token))
            {
                $url .= '?accessToken='.$refresh_token.'&type=googleAdwords';
                Log::info($url);
                return $url;
            }
        }
        catch (Exception $e)
        {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }
    }
    /**
     * @param token, googleToken
     * @return mixed
     */
    function getAccounts($request)
    {
        try
        {
            $businessObj = new BusinessEntity();
            \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();
            // user is not found.
            $user = $userData;
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200)
            {
                return $this->helpError(1, 'Problem in selection of user busienss.');
            }

            $accessToken = urldecode($request->googleToken);
            Log::info($accessToken);
            $businessId = $businessResult['records']['business_id'];
            $client = new Google_Client();

            // Get the list of accounts for the authorized user.
            $tokenDetails = $this->googleAnalyticsEntity->exchangeRefreshToken($accessToken);

            if(!empty($tokenDetails['access_token'])){
                $client->setAccessToken($tokenDetails['access_token']);

                try {
                    $analytics = new Google_Service_Analytics($client);
                    $accounts = $analytics->management_accounts->listManagementAccounts();
                }
                catch (Exception $e) {
                    Log::info($e->getMessage());
                    return $this->helpError(404, 'User does not have any Google Analytics account.');
                }

                if (count($accounts->getItems()) > 0)
                {
                    $items = $accounts->getItems();

                    foreach ($items as $item)
                    {
                        $accountAppendArray[] = [
                            'id' => $item->id,
                            'name' => $item->name,
                            'refresh_token' => $accessToken,
                        ];
                    }

                    return $this->helpReturn("Google Analytics account listing.", $accountAppendArray);
                }
                else
                {
                    return $this->helpError(404, 'User does not have any Google Analytics account.');
                }
            }
            else
            {
                return $this->helpError(3, 'Access token is required.');
            }
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }
        function CampaignService($request){
          /*  try
            {
                $currentDate = Carbon::now()->subMonth(1);
                $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

                $accessToken = urldecode($request->googleToken);
                $tokenDetails = $this->googleAnalyticsEntity->exchangeRefreshToken($accessToken);  //get access token
                if (empty($tokenDetails))
                {
                    return $this->helpError(3, 'Access token required Please try again.');
                }

                $http = new \GuzzleHttp\Client;

                $adsConv = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                    'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId,  'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:RPC', 'dimensions'=>'ga:date']

                ]);
                Log::info("my response");
                $adsConv = json_decode((string)$adsConv->getBody(), true);

                dd($adsConv);
                $ads = new GoogleAds();
                $ads->env('test')
                    ->oAuth([
                        'clientId' => '387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com',
                        'clientSecret' => 'TAIIgMZqPucrc6ZSppPhhQO6',
                        'refreshToken' => $tokenDetails['access_token']

                    ])
                    ->session([
                        'developerToken' => '4lFrHuGLA6bw2hlSUv7-sQ',
                        'clientCustomerId' => '601-644-0298'
                    ]);

                $ads->service(CampaignService::class)
                    ->select(['Id', 'Name', 'Status', 'ServingStatus', 'StartDate', 'EndDate'])
                    ->limit(5)
                    ->get();

                $saved = $ads->report()
                    ->select('CampaignId','AdGroupId','AdGroupName','Id', 'Criteria', 'CriteriaType','Impressions', 'Clicks', 'Cost', 'UrlCustomParameters')
                    ->from('CRITERIA_PERFORMANCE_REPORT');
//                    ->saveToFile($filePath)
//                    ->get();
                dd($saved);
            }
            catch (Exception $e)
            {
                Log::info($e->getMessage());
                return $this->helpError(1, 'Some Problem happened.');
            }*/
        }
    /**
     * @param account_id, refresh_token , token
     * @return all websites that belong to specific account
     */
    function getWebProperties($request)
    {
        try
        {
            $currentDate = Carbon::now()->subMonth(1);
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            $businessObj = new BusinessEntity();
            \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();
            $user = $userData;
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200)
            {
                return $this->helpError(1, 'Problem in selection of user business.');
            }
            $businessId = $businessResult['records']['business_id'];

            if (!isset($request->acountId))
            {
                return $this->helpError(3, 'Account Id Required.');
            }

            if (isset($request->googleToken) && !empty($request->googleToken))
            {

                $tokenDetails = $this->googleAnalyticsEntity->exchangeRefreshToken($request->googleToken);  //get access token

                $http = new \GuzzleHttp\Client;
                $response = $http->request('GET', 'https://www.googleapis.com/analytics/v3/management/accounts/' . $request->acountId . '/webproperties', [
                    'query' => ['access_token' => $tokenDetails['access_token']]
                ]);

                if (!empty($response))
                {
                    $response = json_decode((string)$response->getBody(), true);

                    foreach ($response['items'] as $item)
                    {
                        if(!isset($item['defaultProfileId'])){

                        }
                        else
                        {
                            $propertyAppendArray = [
                                'view_id' => $item['defaultProfileId'],
                                'name' => $item['name'],
                                'website' => $item['websiteUrl'],
                            ];
                        }
                    }
                    Log::info($propertyAppendArray);
                    if(empty($propertyAppendArray))
                    {
                        return $this->helpError(404, 'No Website found of this Google Analytics Account.');
                    }
                    Website::where('business_id' , $businessId)->update(['google_adwords_deleted' => 0, 'gad_connected' => 1]);
                    return $this->helpReturn("Website listing.", $propertyAppendArray);
                }
                else
                {
                    return $this->helpError(404, 'No Website found for this user.');
                }
            }
            else
            {
                return $this->helpError(3, 'Access token required.');
            }
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }
    public function getAllAdsData($request){
        try {
            $currentDate = Carbon::now()->subYear();
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');


            $businessObj = new BusinessEntity();

            \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();
            $user = $userData;

            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200)
            {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $businessId = $businessResult['records']['business_id'];
            $businessWebsite = $businessResult['records']['website'];

            if(empty($businessWebsite))
            {
                return $this->helpError(403, 'Website not setup in your business.');
            }

            if (!isset($request->viewId))
            {
                return $this->helpError(3, 'View Id required.');
            }
            if (!isset($request->googleToken)) {
                return $this->helpError(3, 'Access token required.');
            }

            if (!isset($request->name))
            {
                return $this->helpError(3, 'Name required.');
            }

            if (!isset($request->website))
            {
                return $this->helpError(3, 'Website required.');
            }
            $accessToken = urldecode($request->googleToken);
            $tokenDetails = $this->googleAnalyticsEntity->exchangeRefreshToken($accessToken);  //get access token
            if (empty($tokenDetails))
            {
                return $this->helpError(3, 'Access token required Please try again.');
            }

            $masterAppendArray = [
                'business_id' => $businessId,
                'access_token' => $request->googleToken,
                'profile_id' => $request->viewId,
                'name' => $request->name,
                'website' => $request->website,
                'type' => 'GoogleAdwords',
            ];

           $googleAdwordsId = GoogleAdwordsMaster::create($masterAppendArray);

            $clicks = $this->adsClicks($request, $tokenDetails, $formatedDate, $googleAdwordsId);
            $impressions = $this->adsImpressions($request, $tokenDetails, $formatedDate, $googleAdwordsId);
            $conversionsCost = $this->adsCostPerConversion($request, $tokenDetails, $formatedDate, $googleAdwordsId);
            $revenue = $this->adsRevenue($request, $tokenDetails, $formatedDate, $googleAdwordsId);
            $adsSpend = $this->adsSpend($request, $tokenDetails, $formatedDate, $googleAdwordsId);

            $conversions = StatTracking::where(['google_adwords_id' => $googleAdwordsId->id, 'type' => 'CC'])->count();

            $masterDataArray = [
                'business_id' => $businessId,
                'clicks' => $clicks,
                'impressions' => $impressions,
                'conversions' => $conversions,
                'impression_share' => '0',
                'adsSpend' => $adsSpend,
                'cost_per_conversions' => $conversionsCost,
                'revenue' => $revenue
            ];
            $ads = GoogleAdwordsStats::create($masterDataArray);

            return $this->helpReturn("Ads Data.", $ads);
        }
        catch (Exception $e) {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(404, 'User does not have any Google Ads account.');
        }
    }

    public function adsClicks($request, $tokenDetails, $formatedDate, $googleAdwordsId){
        try {
            $http = new \GuzzleHttp\Client;

            $clicks = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId, 'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:adClicks', 'dimensions'=>'ga:date']

            ]);
            Log::info("my response");
            $clicks = json_decode((string)$clicks->getBody(), true);

            foreach ($clicks['rows'] as $item)
            {
                $t = strtotime($item['0']);
                $activity_date = date('Y-m-d', $t);
                $viewsAppendArray[] = [
                    'google_adwords_id' => $googleAdwordsId->id,
                    'type' => 'CL',
                    'site_type' => 'Googleadwords',
                    'activity_date' => $activity_date,
                    'count' => $item['1'],
                ];
            }

            StatTracking::insert($viewsAppendArray);

            $clicks = $clicks['totalsForAllResults']['ga:adClicks'];
            return  $clicks;
        }
        catch (Exception $e) {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(404, 'User does not have any Google Ads clicks.');
        }
    }

    public function adsImpressions($request, $tokenDetails, $formatedDate, $googleAdwordsId){
        try {
            $http = new \GuzzleHttp\Client;

            $impressions = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId, 'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:impressions', 'dimensions'=>'ga:date']

            ]);
            Log::info("my response");
            $impressions = json_decode((string)$impressions->getBody(), true);

            foreach ($impressions['rows'] as $item)
            {
                $t = strtotime($item['0']);
                $activity_date = date('Y-m-d', $t);
                $viewsAppendArray[] = [
                    'google_adwords_id' => $googleAdwordsId->id,
                    'type' => 'AI',
                    'site_type' => 'Googleadwords',
                    'activity_date' => $activity_date,
                    'count' => $item['1'],
                ];
            }

            StatTracking::insert($viewsAppendArray);
            $impressions = $impressions['totalsForAllResults']['ga:impressions'];
            return $impressions;
        }
        catch (Exception $e) {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(404, 'User does not have any Google Ads clicks.');
        }
    }

    public function adsCostPerConversion($request, $tokenDetails, $formatedDate, $googleAdwordsId){
        try {
            $http = new \GuzzleHttp\Client;

            $costPconv = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId, 'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:costPerConversion', 'dimensions'=>'ga:date']

            ]);
            Log::info("my response");
            $costPconv = json_decode((string)$costPconv->getBody(), true);

            foreach ($costPconv['rows'] as $item)
            {
                $t = strtotime($item['0']);
                $activity_date = date('Y-m-d', $t);
                $viewsAppendArray[] = [
                    'google_adwords_id' => $googleAdwordsId->id,
                    'type' => 'CC',
                    'site_type' => 'Googleadwords',
                    'activity_date' => $activity_date,
                    'count' => $item['1'],
                ];
            }

            StatTracking::insert($viewsAppendArray);
            $costPconv = $costPconv['totalsForAllResults']['ga:costPerConversion'];
            return $costPconv;
        }
        catch (Exception $e) {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(404, 'User does not have any Google Ads clicks.');
        }
    }

    public function adsRevenue($request, $tokenDetails, $formatedDate, $googleAdwordsId){
        try {
            $http = new \GuzzleHttp\Client;

            $adsRev = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId, 'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:RPC', 'dimensions'=>'ga:date']

            ]);
            Log::info("my response");
            $adsRev = json_decode((string)$adsRev->getBody(), true);

            foreach ($adsRev['rows'] as $item)
            {
                $t = strtotime($item['0']);
                $activity_date = date('Y-m-d', $t);
                $viewsAppendArray[] = [
                    'google_adwords_id' => $googleAdwordsId->id,
                    'type' => 'AC',
                    'site_type' => 'Googleadwords',
                    'activity_date' => $activity_date,
                    'count' => $item['1'],
                ];
            }

             StatTracking::insert($viewsAppendArray);

            $adsRev = $adsRev['totalsForAllResults']['ga:RPC'];
            return $adsRev;
        }
        catch (Exception $e) {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(404, 'User does not have any Google Ads clicks.');
        }
    }

    public function adsSpend($request, $tokenDetails, $formatedDate, $googleAdwordsId){
        try {
            $http = new \GuzzleHttp\Client;

            $adsSpend = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId, 'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:adCost', 'dimensions'=>'ga:date']
            ]);
            Log::info("my response");
            $adsSpend = json_decode((string)$adsSpend->getBody(), true);

            foreach ($adsSpend['rows'] as $item)
            {
                $t = strtotime($item['0']);
                $activity_date = date('Y-m-d', $t);
                $viewsAppendArray[] = [
                    'google_adwords_id' => $googleAdwordsId->id,
                    'type' => 'AS',
                    'site_type' => 'Googleadwords',
                    'activity_date' => $activity_date,
                    'count' => $item['1'],
                ];
            }

             StatTracking::insert($viewsAppendArray);

            $adsSpend = $adsSpend['totalsForAllResults']['ga:adCost'];

            return $adsSpend;
        }
        catch (Exception $e) {
            Log::info("i am in catch" .$e->getMessage());
            return $this->helpError(404, 'User does not have any Google Ads clicks.');
        }
    }

    /**
     * @param token
     * @return remove all record of google adwords and state tracking
     */
    function removeGoogleAds($request)
    {
        try
        {
            $analyticsResult = GoogleAdwordsMaster::where('business_id', $request->get('id'))
                ->first();

            if(!empty($analyticsResult['id']))
            {
                StatTracking::where('google_adwords_id', $analyticsResult['id'])->delete();

                GoogleAdwordsMaster::where('id', $analyticsResult['id'])->delete();

                GoogleAdwordsStats::where('business_id', $request->get('id'))->delete();

                Website::where('business_id' , $request->get('id'))->update(['google_adwords_deleted' => 1, 'gad_connected' => 0]);
                return $this->helpReturn("Delete Successfully");
            }
            return $this->helpError(3, 'No Access of this action.');
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }

    function getAdsAllData($request){

        $businessObj = new BusinessEntity();

        \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user = $userData;

        $businessResult = $businessObj->userSelectedBusiness($request);

        if ($businessResult['_metadata']['outcomeCode'] != 200)
        {
            return $this->helpError(1, 'Problem in selection of user business.');
        }

        $businessId = $businessResult['records']['business_id'];
        $businessWebsite = $businessResult['records']['website'];

        if(empty($businessWebsite))
        {
            return $this->helpError(403, 'Website not setup in your business.');
        }

        $adsData = GoogleAdwordsStats::where('business_id', $businessId)->first();

        if(empty($adsData))
        {
            return $this->helpError(403, 'No record found.');
        }else{
            return $this->helpReturn("Ads Data.", $adsData);
        }
    }

    public function getStatsData($request){

        $businessObj = new BusinessEntity();

        \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user = $userData;

        $businessResult = $businessObj->userSelectedBusiness($request);

        if ($businessResult['_metadata']['outcomeCode'] != 200)
        {
            return $this->helpError(1, 'Problem in selection of user business.');
        }

        $businessId = $businessResult['records']['business_id'];
        $businessWebsite = $businessResult['records']['website'];

        if(empty($businessWebsite))
        {
            return $this->helpError(403, 'Website not setup in your business.');
        }

        $ad = GoogleAdwordsMaster::where('business_id', $businessId)->first();

        /********** Cost per conversion *****/
        $graphStatsQueryCurrent = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'CC']);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date')->sum('count');

        $graphStatsQueryLast = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'CC']);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date')->sum('count');

        $totalGraphData = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'CC'])->sum('count');

        if ((int)$totalGraphData > 0){
            $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$totalGraphData;
            $total_CostConversion = $total*100;
        }else{
            $total_CostConversion = 0;
        }
      //  dd($total_CostConversion);
        /********** Cost per conversion *****/

        /********** Clicks *****/
        $graphStatsQueryCurrent = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'CL']);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date')->sum('count');

        $graphStatsQueryLast = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'CL']);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date')->sum('count');

        $totalGraphData = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'CL'])->sum('count');

        if ((int)$totalGraphData > 0){
            $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$totalGraphData;
            $total_Clicks = $total*100;
        }else{
            $total_Clicks = 0;
        }

       // dd($total_Clicks);
        /********** Clicks *****/

        /********** Impressions *****/
        $graphStatsQueryCurrent = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'AI']);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date')->sum('count');

        $graphStatsQueryLast = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'AI']);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date')->sum('count');

        $totalGraphData = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'AI'])->sum('count');

        if ((int)$totalGraphData > 0){
            $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$totalGraphData;
            $total_Impressions = $total*100;
        }else{
            $total_Impressions = 0;
        }

      //  dd($total_Impressions);
        /********** Impressions *****/

        /********** Ads Spend Last Month *****/

        $graphStatsQueryLast = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => 'AS']);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $AdsSpendLastMonth = $graphStatsQueryLast->select('activity_date')->sum('count');

       // dd($graphStatsLastMonth);
        /********** Ads Spend Last Month *****/

        $this->data['costPerConversion'] = (int)$total_CostConversion;
        $this->data['adsClicks'] = (int)$total_Clicks;
        $this->data['adsImpressions'] = (int)$total_Impressions;
        $this->data['AdsSpend'] = (int)$AdsSpendLastMonth;

        return $this->helpReturn("Ads Data.", $this->data);
    }

    public function widgetsGraphs($request){

        $businessObj = new BusinessEntity();

        \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user = $userData;

        $businessResult = $businessObj->userSelectedBusiness($request);

        if ($businessResult['_metadata']['outcomeCode'] != 200)
        {
            return $this->helpError(1, 'Problem in selection of user business.');
        }

        $businessId = $businessResult['records']['business_id'];
        $businessWebsite = $businessResult['records']['website'];

        if(empty($businessWebsite))
        {
            return $this->helpError(403, 'Website not setup in your business.');
        }

        $type = $request->get('type');

        $ad = GoogleAdwordsMaster::where('business_id', $businessId)->first();

        /********** Ads Spend *****/
        $graphStatsQueryCurrent = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => $type]);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date')->sum('count');

        $graphStatsQueryLast = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => $type]);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date')->sum('count');

        $totalGraphData = StatTracking::where(['google_adwords_id' => $ad['id'], 'type' => $request->get('type')])->sum('count');

        if ((int)$totalGraphData > 0){
            $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$totalGraphData;
            $total = $total*100;
        }else{
            $total = 0;
        }

        /********** Ads Spend *****/

        $this->data['total'] = $totalGraphData;
        $this->data['Percent'] = $total;

        return $this->helpReturn("Ads Data.", $this->data);
    }
}
