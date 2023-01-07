<?php

namespace Modules\GoogleAnalytics\Entities;

use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use Modules\Business\Models\Website;
use Modules\ThirdParty\Models\StatTracking;
use Modules\GoogleAnalytics\Models\GoogleAnalyticsMaster;
use Modules\Business\Entities\BusinessEntity;
use Socialite;
use Google_Client;
use Google_Service_People;
use Request;
use DB;
use Config;
use Log;
use Exception;
use Modules\GoogleAnalytics\Entities\Google;
use Google_Service_Analytics;
use Google_Service_Drive;
use GuzzleHttp;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleAnalyticsEntity extends AbstractEntity
{
    use UserAccess;

    protected $googleAnalyticsEntity;

    protected $businessEntity;

    public function __construct()
    {

    }

    function getLogin($request)
    {
        Log::info('google analytics testing');
        Log::info($request);
        $url =  'https://accounts.google.com/o/oauth2/auth?client_id=387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com&redirect_uri=' . config::get('apikeys.CallBack') . '&scope=https://www.googleapis.com/auth/analytics&response_type=code&approval_prompt=force&access_type=offline';
        return $url;
    }

    /**
     * @param $request
     * @return mixed|string
     */
    public function getAccessToken($request)
    {
        try
        {
            $webAppDomain = myDomain();

            Log::info("link > ".public_path());
            $client = new Google_Client();
            $client->setAuthConfig(public_path('client_secret_387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com.json'));

            $client->setApplicationName("Hello Analytics Reporting");

            Log::info($webAppDomain);
            Log::info(" 2 i am here");

            if(isset($request['error']))
            {
                $url = $webAppDomain;
                return $url;
            }

            $client->addScope('https://www.googleapis.com/auth/analytics.manage.users');
            //$client->setAccessType('offline');
            $client->setIncludeGrantedScopes(true);   // incremental auth

            $client->setRedirectUri(config::get('apikeys.CallBack'));

            $client->setApprovalPrompt('consent');

            $client->fetchAccessTokenWithAuthCode($request['code']);

            $webAppDomain = myDomain();

            $url = $webAppDomain;

            Log::info($url);
            Log::info("  i am here");
            $refresh_token = $client->getAccessToken()['refresh_token'];
            Log::info($refresh_token);
            Log::info(" token");

            $refresh_token = urlencode($refresh_token);
            Log::info($refresh_token);
            if (!empty($refresh_token))
            {
                $url .= '?accessToken='.$refresh_token.'&type=googleanalytics';
                Log::info($url);
                return $url;
            }

        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
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
            JWTAuth::setToken($request->input('token'));
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
            $tokenDetails = $this->exchangeRefreshToken($accessToken);

            if(!empty($tokenDetails['access_token'])){
                $client->setAccessToken($tokenDetails['access_token']);

                try {
                    $analytics = new Google_Service_Analytics($client);
                    $accounts = $analytics->management_accounts->listManagementAccounts();
                }
                catch (Exception $e) {
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
            JWTAuth::setToken($request->input('token'));
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
                $tokenDetails = $this->exchangeRefreshToken($request->googleToken);  //get access token

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
                    Website::where('business_id' , $businessId)->update(['google_analytics_deleted' => 0, 'ga_connected' => 1]);
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

    /**
     * @param googleToken,view_id,token,name,website
     * @return all views of specific website
     */
    public function getProfileViews($request)
    {
        try
        {
            $currentDate = Carbon::now()->subYear();
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            $businessObj = new BusinessEntity();

            JWTAuth::setToken($request->input('token'));
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

            $tokenDetails = $this->exchangeRefreshToken($request->googleToken);  //get access token
            if (empty($tokenDetails))
            {
                return $this->helpError(3, 'Access token required Please try again.');
            }

            $http = new \GuzzleHttp\Client;
                       $response = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                'query' => ['access_token' => $tokenDetails['access_token'], 'ids'=>'ga:'.$request->viewId, 'start-date' => $formatedDate, 'end-date'=>'today', 'metrics'=>'ga:pageviews', 'dimensions'=>'ga:date']

            ]);
            Log::info("my response");

            if (!empty($response))
            {
                $response = json_decode((string)$response->getBody(), true);

                $masterAppendArray = [
                    'business_id' => $businessId,
                    'access_token' => $request->googleToken,
                    'profile_id' => $request->viewId,
                    'name' => $request->name,
                    'website' => $request->website,
                    'type' => 'GoogleAnalytics',
                ];

                $googleAnalyticsId = GoogleAnalyticsMaster::create($masterAppendArray);
                foreach ($response['rows'] as $item)
                {
                    $t = strtotime($item['0']);
                    $activity_date = date('Y-m-d', $t);
                    $viewsAppendArray[] = [
                        'google_analytics_id' => $googleAnalyticsId->id,
                        'type' => 'PV',
                        'site_type' => 'Googleanalytics',
                        'activity_date' => $activity_date,
                        'count' => $item['1'],
                    ];
                }

                StatTracking::insert($viewsAppendArray);
                Website::where('business_id' , $businessId)->update(['google_analytics_deleted' => 0, 'ga_connected' => 1]);

                return $this->helpReturn("Views Result.", $viewsAppendArray);
            }
            else
            {
                return $this->helpError(3, 'No Website found for this user.');
            }
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }

    /**
     * @param googleToken
     * @return access_token return
     */
    function exchangeRefreshToken($token)
    {
        try
        {
            if (isset($token) && !empty($token))
            {
                $http = new \GuzzleHttp\Client;
                $response = $http->request('POST', 'https://accounts.google.com/o/oauth2/token', [
                    'form_params' => [
                        'client_id' => '387515964682-hrvlsh7jt667ib8hki3sv4ivvpgdgm7p.apps.googleusercontent.com',
                        'client_secret' => 'TAIIgMZqPucrc6ZSppPhhQO6',
                        'refresh_token' => $token,
                        'grant_type' => 'refresh_token',
                    ]
                ]);
                if (!empty($response))
                {
                    $response = json_decode((string)$response->getBody(), true);
                    return $response;
                }
                else
                {
                    return $this->helpError(3, 'No Website found for this user.');
                }
            }
            else
            {
                return $this->helpError(3, 'Refresh Token Required.');
            }

        }
        catch (Exception $e)
        {
            Log::info('i am in token catch'.$e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }

    /**
     * @param token
     * @return remove all record of google analytics and state tracking
     */
    function removeGoogleAnalytics($request)
    {
        try
        {
            $analyticsResult = GoogleAnalyticsMaster::where('business_id', $request->get('id'))
                ->first();

            if(!empty($analyticsResult['id']))
            {
                StatTracking::where('google_analytics_id', $analyticsResult['id'])->delete();

                GoogleAnalyticsMaster::where('id', $analyticsResult['id'])->delete();

               $web = Website::where('business_id' , $request->get('id'))->update(['google_analytics_deleted' => 1, 'ga_connected' => 0]);
                Log::info('i am in token catch'.$web);
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


    public function getProfileViewsCronJob($request)
    {
        try
        {
            $currentDate = Carbon::now()->subMonth(1);
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');
            $googleAnalytics = GoogleAnalyticsMaster::select('id', 'access_token', 'profile_id')->where('access_token', '!=', null)->where('profile_id', '!=', null);
            $data = $googleAnalytics->get();

            $viewsAppendArray = [];
            $googleAnalyticsIds = [];

            foreach ($data as $row)
            {
                try
                {
                    $tokenDetails = $this->exchangeRefreshToken($row['access_token']);  //get access token
                    $http = new \GuzzleHttp\Client;
                    $response = $http->request('GET', 'https://www.googleapis.com/analytics/v3/data/ga', [
                        'query' => ['access_token' => $tokenDetails['access_token'], 'ids' => 'ga:' . $row['profile_id'], 'start-date' => $formatedDate, 'end-date' => 'today', 'metrics' => 'ga:pageviews', 'dimensions' => 'ga:date']

                    ]);

                    $response = json_decode((string)$response->getBody(), true);

                    foreach ($response['rows'] as $item)
                    {
                        $t = strtotime($item['0']);
                        $activity_date = date('Y-m-d', $t);
                        $viewsAppendArray[] = [
                            'google_analytics_id' => $row['id'],
                            'type' => 'PV',
                            'site_type' => 'Googleanalytics',
                            'activity_date' => $activity_date,
                            'count' => $item['1'],
                        ];

                        $googleAnalyticsIds[] = [
                            'id' => $row['id'],
                        ];
                    }
                }
                catch (Exception $e)
                {
                    Log::info($row['id']);
                    Log::info($e->getMessage());
                }
            }

            if (!empty($viewsAppendArray) && !empty($googleAnalyticsIds))
            {
                StatTracking::whereIn('google_analytics_id', $googleAnalyticsIds)->delete();
                StatTracking::insert($viewsAppendArray);
            }

            return $this->helpReturn("Google Analytics Stats update successfully");

        }
        catch (Exception $e)
        {
            Log::info(" getProfileViewsCronJob > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }
}
