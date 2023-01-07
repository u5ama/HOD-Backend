<?php

namespace Modules\FacebookAds\Entities;

use Modules\ThirdParty\Entities\CustomPersistentDataHandler;
use Session;
use App\Entities\AbstractEntity;
use App\Traits\GlobalResponseTrait;
use Config;
use Facebook\Exceptions\FacebookAuthorizationException;
use FuzzyWuzzy\Fuzz;
use GuzzleHttp\Client;
use Facebook\Exceptions\FacebookClientException;
use Facebook\Exceptions\FacebookOtherException as FacebookOtherException;
use Facebook\Exceptions\FacebookResponseException as FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException as FacebookSDKException;
use Facebook\Exceptions\FacebookServerException;
use Facebook\Exceptions\FacebookThrottleException;
use Facebook\Facebook;
use App\Traits\UserAccess;
use Log;
use Request;
use DB;
use Exception;
use Storage;
use File;
use Illuminate\Http\Response;

class FacebookAdsEntity extends AbstractEntity
{
    use UserAccess;
    const AUTHENTICATION_EXCEPTION = 2000;
    const AUTHORIZATION_EXCEPTION = 2001;
    const CLIENT_EXCEPTION = 2002;
    const OTHER_EXCEPTION = 2003;
    const SERVER_EXCEPTION = 2004;
    const THROTTLE_EXCEPTION = 2005;
    const SDK_EXCEPTION = 2006;
    const FACEBOOK_THROTTLING_LIMIT_EXCEPTION = 2007;
    const FACEBOOK_TOKEN_EXPIRES_EXCEPTION = 2008;
    /**
     * @var Facebook
     */
    private $fb;
    /**
     * @var array
     */
    private $permissions;
    private $accessToken;
    protected $hidden = ['access_token'];

    public function __construct()
    {
        $this->permissions = ['email', 'ads_management', 'ads_read', 'read_insights'];
        $this->fb = new Facebook(
            [
                'app_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                'app_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                'default_graph_version' => 'v8.0',
                'persistent_data_handler' => new CustomPersistentDataHandler(),
            ]
        );
    }

    /**
     * @param $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    private function getAccessToken()
    {
        return $this->accessToken;
    }

    /*
  * getlogin -> callback
  */
    public function getLogin($request)
    {
        try {
            $apiUrl =  url('/') . '/api/fb-reports/callback';

            $helper = $this->fb->getRedirectLoginHelper();


            Log::info("POST " . $request->get('referType'));

            // Code Change after discussion with Facebook Dev - Get business id through session

            /**
             * This flag is made for to allow guest user or to make this login happen at
             * multiple screen. so this refer type will indicate in next where user will be go.
             */
            if(!empty($request->get('referType')) && ( $request->get('referType') == 'social_post_settings' || $request->get('referType') == 'weekly_report' || $request->get('referType') == 'posts' || $request->get('referType') == 'posts_demo' || $request->get('referType') == 'home' || $request->get('referType') == 'get_started' ))
            {
                $business_id = $request->get('business_id');
                $request->session()->put('business_id', $business_id);
                $request->session()->put('referType', $request->get('referType'));
            }
            else
            {
                $business_id = $request->get('business_id');
                $request->session()->put('business_id', $business_id);
            }

            $request->session()->save();

            $loginUrl =$helper->getLoginUrl($apiUrl, $this->permissions);
            Log::info("loginURL " . $loginUrl);
            return response()->json($loginUrl);
        }
        catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }

        catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (Exception $e) {
            Log::info(" getLogin " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Please Try again.');
        }
    }

    /**
     * @param $id (business_id)
     * @return string
     */
    public function getToken($id, $referType = '')
    {
        Log::info("get Token ");
        try{
            $url = '';
            $accessToken = '';
            $webAppDomain = myDomain();
            /*if($referType == 'posts')
            {
                $url = $webAppDomain . '/social-posts';
            }
            elseif($referType == 'posts_demo')
            {
                $url = $webAppDomain . '/posts-demo';
            }
            elseif($referType == 'social_post_settings')
            {
                $url = $webAppDomain . '/social-media';
            }
            elseif($referType == 'get_started')
            {
                $url = $webAppDomain . '/practice-profile';
            }*/
//            if
//            {
                $url = $webAppDomain . '/connections';
//            }
            Log::info('our url');
            // Log::info($url);

            $redirect_url = url('/') . '/api/fb-reports/callback';
            Log::info($redirect_url);

            $helper = $this->fb->getRedirectLoginHelper();
            $_SESSION['FBRLH_state']=$_GET['state'];
            if (Request::has('code')) {
                Log::info('code here');
                Log::info(Request::has('code'));
                /**
                Code Change after discussion with Facebook Dev - Get facebook state through session
                 */
                Log::info('im here');
                Log::info($_GET['state']);
                if (isset($_GET['state'])) {
                    $helper->getPersistentDataHandler()->set('state', $_GET['state']);
                }

                $accessToken = $helper->getAccessToken($redirect_url);

                Log::info('token');
                Log::info($accessToken);

                $url .= '?accessRepToken=' . $accessToken . '&type=facebook';

            }

            Log::info('before return check url');
            Log::info($url);
            Log::info('afterend');
            $urlArray =
                [
                    'url' => $url,
                ];
            return $this->helpReturn('Get Url Successfully', $urlArray);
        }
        catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }

        catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (Exception $e) {
            Log::info(" getToken > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Please Try again.');
        }
    }

    public function getUserAccessToken()
    {
        $helper = $this->fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();
    }

    /**
     * Get User Reports.
     * @param $request (access_token)
     * @return mixed
     */
    public function getAdsReports($request){

        try {
            $access_token = $request->get('access_token');
            if ($access_token != '') {

                $account_id = $this->fb->get('/me?fields=id&access_token='.$access_token);

                $results = $account_id->getDecodedBody();

                $results = array_filter($results);
                $account_id = $results['id'];

                $ad_id = $this->fb->get('/me?fields=account_id&access_token='.$access_token);

                dd($ad_id);
                $response = $this->fb->get('/'.$results['id'].'/insights',
                    $access_token
                );
                dd($response);
            }
        }
        catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
        catch (Exception $e) {
            Log::info(" getReports > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
    }
}
