<?php
namespace Modules\ThirdParty\Entities;
use Session;
use App\Entities\AbstractEntity;
use App\Traits\GlobalResponseTrait;
use Config;
use Facebook\Exceptions\FacebookAuthorizationException;
use FuzzyWuzzy\Fuzz;
use GuzzleHttp\Client;
use Modules\Business\Entities\BusinessEntity;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Facebook\Exceptions\FacebookClientException;
use Facebook\Exceptions\FacebookOtherException as FacebookOtherException;
use Facebook\Exceptions\FacebookResponseException as FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException as FacebookSDKException;
use Facebook\Exceptions\FacebookServerException;
use Facebook\Exceptions\FacebookThrottleException;
use Facebook\Facebook;
use App\Traits\UserAccess;
use Carbon\Carbon;
use Log;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\TripAdvisorEntity;
use Request;
use DB;
use Exception;
use Storage;
use File;
use Modules\ThirdParty\Models\PostMaster;
use Illuminate\Http\Response;
class FacebookEntity extends AbstractEntity
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
    protected $businessEntity;

    public function __construct()
    {
        $this->permissions = ['email', 'read_insights', 'pages_show_list', 'pages_read_engagement','pages_read_user_content'];
        $this->fb = new Facebook(
            [
                'app_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                'app_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                'default_graph_version' => 'v4.0',
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
            $apiUrl =  url('/') . '/api/social-media/callback';

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


    public function getUserAccessToken()
    {
        $helper = $this->fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();
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
            if($referType == 'posts')
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
            }
            else
            {
                $url = $webAppDomain . '/connections';
            }
            Log::info('our url');
           // Log::info($url);

            $redirect_url = url('/') . '/api/social-media/callback';
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

                $url .= '?accessToken=' . $accessToken . '&type=facebook';

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

    /**
     * Get User Pages.
     * @param $request (access_token)
     * @return mixed
     */
    public function getPageList($request)
    {
        try {
            $access_token = $request->get('access_token');
            if ($access_token != '') {

                $pageList = $this->fb->get('/me/accounts', $access_token);

                $results = $pageList->getDecodedBody();

                $results = array_filter($results);

                if($results)
                {
                    foreach ($results['data'] as $index => $page)
                    {
                        $page_id = $page['id'];

                        $pagePhoto = $this->fb->get('/' . $page_id . '?fields=picture', $access_token);

                        $pageAddress = $this->fb->get('/' . $page_id . '?fields=location', $access_token);


                        $pagePhone = $this->fb->get('/' . $page_id . '?fields=phone', $access_token);

                        $pageLikes = $this->fb->get('/' . $page_id . '?fields=fan_count', $access_token);

                        $pageReviews = $this->fb->get('/' . $page_id . '?fields=overall_star_rating', $access_token);
                        $pageRating = $this->fb->get('/' . $page_id . '?fields=rating_count', $access_token);

                        $pagePhoto = $pagePhoto->getDecodedBody();
                        $pageAddress =  $pageAddress->getDecodedBody();
                        $pagePhone =  $pagePhone->getDecodedBody();
                        $pageRating = $pageRating->getDecodedBody();
                        $pageLikes = $pageLikes->getDecodedBody();
                        $pageReviews = $pageReviews->getDecodedBody();


                        $results['data'][$index]['average_rating'] = !empty($pageRating['rating_count']) ? $pageRating['rating_count'] : '';
                        $results['data'][$index]['page_likes_count'] = !empty($pageLikes['fan_count']) ? $pageLikes['fan_count'] : '';
                        $results['data'][$index]['page_reviews_count'] = !empty($pageReviews['overall_star_rating']) ? $pageReviews['overall_star_rating'] : '';

                        $results['data'][$index]['address'] = !empty($pageAddress['location']['street']) ? $pageAddress['location']['street'] : '';
                        $results['data'][$index]['city'] = !empty($pageAddress['location']['city']) ? $pageAddress['location']['city'] : '';
                        $results['data'][$index]['zipcode'] = !empty($pageAddress['location']['zip']) ? $pageAddress['location']['zip'] : '';
                        $results['data'][$index]['country'] = !empty($pageAddress['location']['country']) ? $pageAddress['location']['country'] : '';

                        $results['data'][$index]['phone'] = !empty($pagePhone['phone']) ? $pagePhone['phone'] : '';
                        $results['data'][$index]['logo'] = !empty($pagePhoto['picture']) ? $pagePhoto['picture']['data']['url'] : '';

                    }
                    return $this->helpReturn('Page Result', $results);
                }
                else
                {
                    $socialEntityObj = new SocialEntity();
                    $socialEntityObj->manageSocialBusinessPages($request, 'facebook');

                    return $this->helpError(404, 'No Page found of this account.');
                }
            }
            else {
                return $this->helpError(2, 'Access token is missing');
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
            Log::info(" getPageList > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
    }

    /**
     * @param $request (access_token, pageId)
     * @return mixed
     */
    public function getPageDetail($request)
    {
        try {
            $accessToken = $request->get('access_token');

            $currentDate = Carbon::tomorrow();
            $tilldate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            $currentDate = Carbon::now()->subMonth(3);
            $since_date = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            $currentDatee = Carbon::now()->subMonth(1);
            $since_insight_date= Carbon::createFromFormat('Y-m-d H:i:s', $currentDatee)->format('Y-m-d');
            if ($accessToken == '') {
                return $this->helpError(2, 'Access token is missing');
            }

            $pageList = $this->fb->get('/me/accounts', $accessToken);

            $results = $pageList->getDecodedBody();

            $results = array_filter($results);


            if (!empty($request->get('page_id'))) {
                $pageId = $request->get('page_id');

                Log::info($pageId);
                Log::info('page id');
                if ($results) {

                    $pageBasicDetail = $this->fb->get('/' . $pageId . '?fields=name,phone,location, link,overall_star_rating,fan_count,picture, cover, website', $accessToken);
                    $pageAccessToken = $this->fb->get('/' . $pageId . '?fields=access_token', $accessToken);
                    $pageAccessToken = $pageAccessToken->getDecodedBody();
                    $pageAccessShortLifeToken = $pageAccessToken['access_token'];

                    $params = [
                        'grant_type' => 'fb_exchange_token',
                        'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                        'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                        'fb_exchange_token' => $pageAccessShortLifeToken,

                    ];
                    $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $accessToken);
                    $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();
                    Log::info($longTimeAccessToken);
                    Log::info('Token');
                    $pageRecomendationDetail = $this->fb->get('/' . $pageId . '/ratings', $longTimeAccessToken['access_token']);

                    $pageLikeDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_date . '&until=' . $tilldate . '&metric=page_fan_adds', $longTimeAccessToken['access_token']);

                    /**/
                    $pagePostDetail = $this->fb->get('/' . $pageId . '/posts', $longTimeAccessToken['access_token']);

                    $pageViewsDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_insight_date . '&until=' . $tilldate . '&metric=page_views_total&limit=9999&period=days_28', $longTimeAccessToken['access_token']);
                    $pageTotalReachDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_insight_date . '&until=' . $tilldate . '&metric=page_impressions_unique&limit=9999&period=days_28', $longTimeAccessToken['access_token']);
                    $pagePeopleEngagedDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_insight_date . '&until=' . $tilldate . '&metric=page_engaged_users&limit=9999&period=days_28', $longTimeAccessToken['access_token']);

                    $pageRecomendationDetail = $pageRecomendationDetail->getDecodedBody();

                    $recommendationCount= count($pageRecomendationDetail['data']);

                    $pageBasicDetail = $pageBasicDetail->getDecodedBody();
                    $pageLikeDetail = $pageLikeDetail->getDecodedBody();

                    Log::info($pageLikeDetail);

                    $pagePostDetail = $pagePostDetail->getDecodedBody();

                    $pageViewsDetail = $pageViewsDetail->getDecodedBody();

                    $pageTotalReachDetail = $pageTotalReachDetail->getDecodedBody();
                    $pagePeopleEngagedDetail = $pagePeopleEngagedDetail->getDecodedBody();

                    $appendLikeArray['likes_data'] = $pageLikeDetail;
                    $appendPostArray['post_data'] = $pagePostDetail;

                    $appendPageRecommendationArray['page_recommendation_data'] = $pageRecomendationDetail;
                    $appendPageRecommendationCount['recommendation_count'] = $recommendationCount;
                    $appendPageViewsArray['page_views_data'] = $pageViewsDetail;
                    $appendTotalReachArray['total_reach_data'] = $pageTotalReachDetail;
                    $appendPeopleEngagedArray['people_engaged_data'] = $pagePeopleEngagedDetail;

                    $appendlongLifeAccessToken['long_life_access_token'] = $longTimeAccessToken;

                    $finalDetails = array_merge($pageBasicDetail, $appendPageRecommendationCount, $appendPageRecommendationArray, $appendLikeArray,$appendPostArray,$appendPageViewsArray,$appendTotalReachArray,$appendPeopleEngagedArray, $appendlongLifeAccessToken);

                    return $this->helpReturn('Page Result', $finalDetails);

                } else {

                    return $this->helpError(404, 'No Page found of this account.');
                }
            } else {
                return $this->helpError(2, 'page id is Missing');
            }
        }

        catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem found. Please Try again.');
        }

    }

    /**
     * Save data of facebook in our system.
     * @param Request $request (business_id,businessKeyword, userId)
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeThirdPartyMaster($request)
    {
        Log::info("Business Facebook Register Process started" . json_encode($request->all()));

        try {
            $tripEntity = new TripAdvisorEntity();
            $thirdPartyEntity = new ThirdPartyEntity();

            // get business detail from trip advisor.
            $result = $this->getBusinessDetail($request);
            $responseCode = $result['_metadata']['outcomeCode'];

            //  $data = [];
            $data['type'] = 'Facebook';

            if ($responseCode == 200) {
                $records = $result['records']['Results'];

                $fuzz = new Fuzz();

                if ($records) {

                    $score = $fuzz->tokenSortRatio($request->get('name'), $records['Name']);

                    Log::info("FB Scrapper -> Score of -> $score > Business Name > " . $request->get('name') . " > FB Scrapper Name " . $records['Name']);

                    if ($score >= 40) {
                        Log::info("Ok for FB sc");

                        $businessId = $request->get('business_id');

                        $data['business_id'] = $businessId;
                        $data['name'] = $records['Name'];
                        $data['page_id'] = (!empty($records['Other']['id'])) ? $records['Other']['id'] : "";
                        $data['page_url'] = $records['URL'];
                        $data['page_reviews_count'] = $records['Review'];
                        $data['average_rating'] = $records['Rating'];
                        $data['page_likes_count'] = (!empty($records['Other']['Likes'])) ? $records['Other']['Likes'] : 0;
                        $data['website'] = $records['Website'];
                        $data['phone'] = $records['ContactNo'];
                        $data['street'] = $records['AddressDetail']['Street'];
                        $data['city'] = $records['AddressDetail']['City'];
                        $data['zipcode'] = $records['AddressDetail']['Zip'];
                        $data['state'] = $records['AddressDetail']['State'];
                        $data['country'] = $records ['AddressDetail']['Country'];

                        $data['is_manual_connected'] = 0;


                        // if is_silhouette == 1 then it has not any profile picture
                        if (!empty($records['Other']['picture']['data']['is_silhouette']) && $records['Other']['picture']['data']['is_silhouette'] == 1) {
                            $data['profile_photo'] = '';
                            $profilePicture = 0;
                        } else {
                            $data['profile_photo'] = !empty($records['Other']['picture']) ? $records['Other']['picture']['data']['url'] : '';
                            $profilePicture = 1;
                        }

                        $data['cover_photo'] = (!empty($records['Other']['cover'])) ? $records['Other']['cover']['source'] : '';
                        $data['add_review_url'] = $records['URL'] . '/reviews/?ref=page_internal';

                        /**
                         * Remove http in website before saving into database
                         */
                        $str = $data['website'];
                        $str = preg_replace('#^http?://#', '', rtrim($str, '/'));

                        $data['website'] = $str;

                        $thirdPartyResult = SocialMediaMaster::create($data);

                        $thirdPartyId = (!empty($thirdPartyResult['id'])) ? $thirdPartyResult['id'] : NULL;

                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $thirdPartyResult['phone'],
                                'issue' => ($thirdPartyResult['phone'] != '') ? 18 : 49,
                                'oldIssue' => ($thirdPartyResult['phone'] == '') ? 18 : 49
                            ],
                            [
                                'key' => 'address',
                                'value' => $thirdPartyResult['street'],
                                'issue' => ($thirdPartyResult['street'] != '') ? 19 : 51,
                                'oldIssue' => ($thirdPartyResult['street'] == '') ? 19 : 51
                            ],
                            [
                                'key' => 'website',
                                'value' => $thirdPartyResult['website'],
                                'issue' => ($thirdPartyResult['website'] != '') ? 20 : 50,
                                'oldIssue' => ($thirdPartyResult['website'] == '') ? 20 : 50
                            ],
                            [
                                'key' => 'profile_photo',
                                'value' => $profilePicture,
                                'issue' => 22,
                            ],
                            [
                                'key' => 'cover_photo',
                                'value' => $thirdPartyResult['cover_photo'],
                                'issue' => 23,
                            ],
                            [
                                'key' => 'reviews',
                                'value' => $thirdPartyResult['page_reviews_count'],
                                'issue' => 64,
                                'oldIssue' => ""
                            ],
                            [
                                'key' => 'rating',
                                'value' => $thirdPartyResult['average_rating'],
                                'issue' => 60,
                                'oldIssue' => ""
                            ]
                        ];

                        $thirdPartyEntity->globalIssueGenerator($request->get('userID'), $businessId, $thirdPartyId, $issueData, $data['type'], 'social');

                        // here is the new check come for new issues
                        return $this->helpReturn("Facebook record save.");
                    }
                    else
                    {
                        Log::info("FB Name accuracy issue");
                        $responseCode = 404;
                    }
                }
            }

            if($responseCode == 404 || $responseCode == 1) {
                $businessId = $request->get('business_id');
                $data['business_id'] = $businessId;
                $insertIssue = [
                    [
                        'key' => 'name',
                        'userID' => $request->get('userID'),
                        'business_id' => $businessId,
                        'issue' => 17,
                        'type' => $data['type']
                    ]
                ];

                $tripEntity->compareThirdPartyRecord($insertIssue, 'social');
            }

            return $this->helpError(404, 'Record not found.');
        }
        catch (Exception $e)
        {
            Log::info("FacebookEntity > storeThirdPartyMaster >> " . $e->getMessage());

            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * Get data from facebook of given business name.
     * @param $request
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBusinessDetail($request)
    {
        try {
            if($request->has('businessKeyword'))
            {
                $businessKeyword = $request->get('businessKeyword');
            }
            elseif($request->has('name'))
            {
                $businessKeyword = $request->get('name');
            }

            $query = ['Keyword' => $businessKeyword];

            if($request->has('phone'))
            {
                $query['PhoneNo'] = $request->get('phone');
            }

            Log::info("fb query " . json_encode($query));


            $appEnvironment = Config::get('apikeys.APP_ENV');

            $serverUrl = ( $appEnvironment == 'production') ? Config::get('custom.Scrapper_Prod_SERVER_URL'): Config::get('custom.SERVER_URL');


            $detailUrl = ( $appEnvironment == 'production') ? Config::get('custom.facebookProdBusinessDetail'): Config::get('custom.facebookTestBusinessDetail');

            $url = $serverUrl.$detailUrl;

            Log::info("$url " . $url);

            $client = new Client([]);

            $response = $client->request(
                'GET',
                $url,
                [
                    'query' => $query
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() == 200) {
                if (!empty(($responseData['Results']['Name']))) {
                    return $this->helpReturn("Facebook Response.", $responseData);
                }
            }

            return $this->helpError(404, 'Record not found.');
        } catch (Exception $e) {
            Log::info("facebookentity > getBusinessDetail >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * Get User Page Post
     * @param $request (access_token, pageId)
     * @return mixed
     */

    public function getPagePostInfo($pagToken, $pageId, $type)
    {
        $access_token = $pagToken;
        $page_id = $pageId;

        if ($access_token != '') {
            try {
                if ($page_id != '') {

                    $getpagepost = $this->fb->get('/' . $page_id . '?fields=posts', $access_token);

                    $pagepost = $getpagepost->getDecodedBody();

                    if( !empty($pagepost['posts']['data']) ) {
                        $userPageDetail['posts'] = $pagepost['posts']['data'];
                        return $this->helpReturn('Results are.', $userPageDetail);
                    }

                    return $this->helpError(404, 'page posts not found.');
                } else {
                    return $this->helpError(2, 'page id is Missing');
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
                Log::info($e->getMessage());
                return $this->helpError(1, 'Some Problem happened. Record not found.');
            }
        } else
        {
            return $this->helpError(2, 'Token is Missing');
        }
    }

    public function getPageReviewRatingInfo($pagToken, $pageId, $type)
    {

        $accessToken = $pagToken;
        $page_id = $pageId;

        if ($accessToken != '') {
            try {

                if ($page_id != '') {

                    $pageAccessToken = $this->fb->get('/' . $pageId . '?fields=access_token', $accessToken);
                    $pageAccessToken = $pageAccessToken->getDecodedBody();
                    $pageAccessShortLifeToken = $pageAccessToken['access_token'];

                    $params = [
                        'grant_type' => 'fb_exchange_token',
                        'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                        'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                        'fb_exchange_token' => $pageAccessShortLifeToken,

                    ];
                    $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $accessToken);
                    $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();

                    $pageRecommendation = $this->fb->get('/' . $pageId . '/ratings', $longTimeAccessToken['access_token']);
                    $pageRecommendation = $pageRecommendation->getDecodedBody();
                    $recommendationCount= count($pageRecommendation['data']);

                    $getPageLikesCount = $this->fb->get('/' . $page_id . '?fields=fan_count', $longTimeAccessToken['access_token']);
                    $getPageLikesCount = $getPageLikesCount->getDecodedBody();

                    $getPageUrl = $this->fb->get('/' . $page_id . '?fields=link', $longTimeAccessToken['access_token']);
                    $getPageUrl = $getPageUrl->getDecodedBody();

                    $appendPageRecommendationCount['page_recommendation_count'] = $recommendationCount;
                    $appendPageLikesCount['page_likes_count'] = $getPageLikesCount;
                    $appendPageUrl['page_url'] = $getPageUrl;

                    $appendLongLifeAccessToken['long_life_access_token'] = $longTimeAccessToken;

                    $finalDetails = array_merge($appendPageRecommendationCount,$appendPageLikesCount, $appendPageUrl,$appendLongLifeAccessToken);

                    return $this->helpReturn('Results are.', $finalDetails);

                } else {
                    Log::info('page id is Missing');
                    return $this->helpError(2, 'page id is Missing');
                }


            } catch (Exception $e) {
                Log::info($e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        } else {
            Log::info('Token is Missing');
            return $this->helpError(2, 'Token is Missing');
        }
    }

    public function getPageReviewRatingLikeHistoricalData($request)
    {
        try {
            $pageAccessToken = $request->get('page_access_token');

            $currentDate = Carbon::tomorrow();
            $tilldate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            $currentDate = Carbon::now()->subMonth(3);
            $since_date = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');


            $currentDatee = Carbon::now()->subMonth(1);
            $since_insight_date= Carbon::createFromFormat('Y-m-d H:i:s', $currentDatee)->format('Y-m-d');

            if ($pageAccessToken == '') {
                Log::info("Page Access token is missing");
                return $this->helpError(2, 'Page Access token is missing');
            }

            if (!empty($request->get('page_id'))) {
                $pageId = $request->get('page_id');
                $params = [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                    'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                    'fb_exchange_token' => $pageAccessToken,
                ];

                $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $pageAccessToken);
                $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();

                $pageRecomendationDetail = $this->fb->get('/' . $pageId . '/ratings', $longTimeAccessToken['access_token']);
                $pageLikeDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_date . '&until=' . $tilldate . '&metric=page_fan_adds&limit=9999', $longTimeAccessToken['access_token']);
                $pagePostDetail = $this->fb->get('/' . $pageId . '/posts', $longTimeAccessToken['access_token']);
                $pageViewsDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_insight_date . '&until=' . $tilldate . '&metric=page_views_total&limit=9999&period=days_28', $longTimeAccessToken['access_token']);
                $pageTotalReachDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_insight_date . '&until=' . $tilldate . '&metric=page_impressions_unique&limit=9999&period=days_28', $longTimeAccessToken['access_token']);
                $pagePeopleEngagedDetail = $this->fb->get('/' . $pageId . '/insights?pretty=0&since=' . $since_insight_date . '&until=' . $tilldate . '&metric=page_engaged_users&limit=9999&period=days_28', $longTimeAccessToken['access_token']);

                $pageRecomendationDetail = $pageRecomendationDetail->getDecodedBody();
                $recommendationCount= count($pageRecomendationDetail['data']);
                $pageLikeDetail = $pageLikeDetail->getDecodedBody();
                $pagePostDetail = $pagePostDetail->getDecodedBody();

                $pageViewsDetail = $pageViewsDetail->getDecodedBody();
                $pageTotalReachDetail = $pageTotalReachDetail->getDecodedBody();
                $pagePeopleEngagedDetail = $pagePeopleEngagedDetail->getDecodedBody();

                $appendLikeArray['likes_data'] = $pageLikeDetail;
                $appendPostArray['post_data'] = $pagePostDetail;

                $appendPageRecommendationArray['page_recommendation_data'] = $pageRecomendationDetail;
                $appendPageRecommendationCount['recommendation_count'] = $recommendationCount;

                $appendPageViewsArray['page_views_data'] = $pageViewsDetail;
                $appendTotalReachArray['total_reach_data'] = $pageTotalReachDetail;
                $appendPeopleEngagedArray['people_engaged_data'] = $pagePeopleEngagedDetail;

                $appendlongLifeAccessToken['long_life_access_token'] = $longTimeAccessToken;

                $finalDetails = array_merge($appendPageRecommendationArray, $appendLikeArray,$appendPostArray,$appendPageViewsArray,$appendTotalReachArray,$appendPeopleEngagedArray, $appendlongLifeAccessToken);

                return $this->helpReturn('Page Result', $finalDetails);

            } else {
                Log::info("page id is Missing");
                return $this->helpError(2, 'page id is Missing');
            }
        } catch (Exception $e) {

            Log::info("error in long life token");
            return $this->helpError(1, 'Some Problem happened. Please Try Again');

        }
    }

    public function getPageBasicInfo($pagToken, $pageId, $type)
    {

        $access_token = $pagToken;
        $page_id = $pageId;

        if ($access_token != '') {
            try {

                if ($page_id != '') {

                    $getpagepost = $this->fb->get('/' . $page_id . '?fields=name,phone,location, picture, cover, website', $access_token);
                    $pageContent = $getpagepost->getDecodedBody();


                } else {
                    Log::info('page id is Missing');
                    return $this->helpError(2, 'page id is Missing');
                }

                return $this->helpReturn('Results are.', $pageContent);
            } catch (Exception $e) {
                Log::info($e->getMessage());
                return $this->helpError(404, 'Record not found.');
            }
        } else {
            Log::info('Token is Missing');
            return $this->helpError(2, 'Token is Missing');
        }
    }

    public function directPublishedPost($request)
    {
        try {
            Log::info('Your are in Facebook');
            $businessId = $request['business_id'];
            $post_message = $request['message'];
            $socialMedia = SocialMediaMaster::select('business_id', 'access_token', 'page_access_token', 'page_id')
                ->where('business_id', $businessId)
                ->whereNotNull('page_access_token')
                ->whereNotNull('page_id')
                ->whereNotNull('access_token')
                ->first();
            $data['page_access_token'] = $socialMedia['page_access_token'];
            $data['user_access_token'] = $socialMedia['access_token'];
            $data['page_id'] = $socialMedia['page_id'];

            if ($request->status == 'published' && !isset($request->post_id)) {
                /**
                 * Direct Post To Twitter
                 */
                $media_ids = [];
                $photoIdArray = [];
                $pageAccessToken = $this->fb->get('/' . $data['page_id'] . '?fields=access_token', $data['user_access_token']);
                $pageAccessToken = $pageAccessToken->getDecodedBody();
                $pageAccessShortLifeToken = $pageAccessToken['access_token'];
                $params = [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                    'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                    'fb_exchange_token' => $pageAccessShortLifeToken,
                ];
                $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $data['user_access_token']);
                $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();
                $mediaType = '';
                if ($request->hasFile('attach_file')) {
                    $attachedFile = $request->attach_file;
                    foreach ($attachedFile as $file) {

                        Log::info('Direct published without post id');
                        $extension = $file->getClientOriginalExtension();
                        if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'JPEG' || $extension == 'png' || $extension == 'PNG') {
                            $mediaType = 'image';
                        } else if ($extension == 'mp4') {
                            $mediaType = 'video';
                        }
                        if (isset($mediaType) && $mediaType == 'image') {

                            $uploadImageFeed = $this->fb->post('/' . $data['page_id'] . '/photos', ['source' => $this->fb->fileToUpload($file), 'published' => FALSE, 'caption' => $post_message], $longTimeAccessToken['access_token']);
                            $uploadImageFeed = $uploadImageFeed->getDecodedBody();
                            $media_ids[] = $uploadImageFeed['id'];
                        } else if (isset($mediaType) && $mediaType == 'video') {
                            $postVideoFeed = $this->fb->post('/' . $data['page_id'] . '/videos', ['source' => $this->fb->videoToUpload($file), 'description' => $post_message], $longTimeAccessToken['access_token']);
                            $post = $postVideoFeed->getDecodedBody();
                        }
                    }
                }
                $postImageParams = [
                    'message' => $post_message
                ];
                if ($mediaType == 'image') {

                    if (!empty($media_ids)) {
                        foreach ($media_ids as $index => $photoId) {
                            $postImageParams["attached_media"][$index] = '{"media_fbid":"' . $photoId . '"}';
                        }
                    }
                }
                if ($mediaType != 'video') {

                    $posts = $this->fb->post('/' . $data['page_id'] . '/feed', $postImageParams, $longTimeAccessToken['access_token']);
                    $post = $posts->getDecodedBody();
                }
                PostMaster::create(['business_id' => $businessId, 'post_id' => $post['id'], 'social_media_type' => 'Facebook', 'status' => $request->status]);
            }

        } catch (Exception $e) {
            Log::info(" directPublishedPost > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Please Try again.');
        }
    }

    public function indirectPublishedPost($request)
    {
        try{
            Log::info('check request');
            Log::info($request);
        $businessId = $request['business_id'];
        $post_message = $request['message'];
        $socialMedia = SocialMediaMaster::select('business_id','access_token','page_access_token','page_id')
            ->where('business_id',$businessId)
            ->whereNotNull('page_access_token')
            ->whereNotNull('page_id')
            ->whereNotNull('access_token')
            ->first();
        Log::info('all record');
        Log::info($socialMedia);
        $data['page_access_token'] = $socialMedia['page_access_token'];
        $data['user_access_token'] = $socialMedia['access_token'];
        $data['page_id'] = $socialMedia['page_id'];

        $pageAccessToken = $this->fb->get('/' .  $data['page_id'] . '?fields=access_token',  $data['user_access_token']);
        $pageAccessToken = $pageAccessToken->getDecodedBody();
        $pageAccessShortLifeToken = $pageAccessToken['access_token'];
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
            'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
            'fb_exchange_token' => $pageAccessShortLifeToken,
        ];
        $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params,  $data['user_access_token']);
        $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();
        $mediaType = '';
        $record = [];
        if ($request->status == 'published' && isset($request->post_id)) {
            Log::info('post with published and post id');
            //$postMastersRecord = PostMasterSocialMedia::where('post_master_id', $request->post_id)->get()->toArray();

            $attachment = $request['urls'];
            if (!empty($attachment)) {

                foreach ($attachment as $file) {
                    $mediaType = $file['type'];
                    if(isset($mediaType) && $mediaType == 'image'){
                        $request->request->add(['media_type' => 'image']);
                        $uploadImageFeed = $this->fb->post('/' . $data['page_id'] . '/photos', ['source' => $this->fb->fileToUpload($file['media_url']), 'published' => FALSE, 'caption' => $post_message], $longTimeAccessToken['access_token']);
                        $uploadImageFeed = $uploadImageFeed->getDecodedBody();
                        $media_ids[] = $uploadImageFeed['id'];

                    }else if(isset($mediaType) && $mediaType == 'video'){

                        $postVideoFeed = $this->fb->post('/' . $data['page_id'] . '/videos', ['source' => $this->fb->videoToUpload($file['media_url']), 'description'=> $post_message],  $longTimeAccessToken['access_token']);
                        $post = $postVideoFeed->getDecodedBody();

                    }

                }
            }


            $postImageParams = [
                'message' => $post_message
            ];
            if ($mediaType == 'image') {

                if (!empty($media_ids)) {
                    foreach ($media_ids as $index => $photoId) {
                        $postImageParams["attached_media"][$index] = '{"media_fbid":"' . $photoId . '"}';
                    }
                }
            }

            if ($mediaType != 'video') {

                $posts = $this->fb->post('/' . $data['page_id'] . '/feed', $postImageParams, $longTimeAccessToken['access_token']);

                $post = $posts->getDecodedBody();
            }
            $record = ['business_id' => $businessId, 'post_id' => $post['id'], 'social_media_type' => 'Facebook', 'status' => $request->status];


        }

        return $this->helpReturn("Post Successfully Added.",$record);

        }
        catch (Exception $exception)
        {
            Log::info($exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getSocialMediaPostFeed($data)
    {
        try {
            $businessId = $data['businessId'];

            $socialResult = SocialMediaMaster::select('business_id','type', 'name', 'access_token', 'page_access_token', 'page_id')
                ->where('type','Facebook')
                ->where('business_id', $businessId)
                ->whereNotNull('page_access_token')
                ->whereNotNull('page_id')
                ->whereNotNull('access_token')
                ->get()->toArray();

            if (empty($socialResult)) {
                return $this->helpError(404, 'No Page found of this account.');
            }

            $postIdArray  = [];
            $postresult  = [];

            $result=[];

            $data['page_access_token'] = $socialResult[0]['page_access_token'];
            $data['user_access_token'] = $socialResult[0]['access_token'];
            $data['page_id'] = $socialResult[0]['page_id'];

            if ($data['user_access_token'] != '') {
                $pageAccessToken = $this->fb->get('/' . $data['page_id'] . '?fields=access_token', $data['user_access_token']);
                $pageAccessToken = $pageAccessToken->getDecodedBody();
                $pageAccessShortLifeToken = $pageAccessToken['access_token'];

                $params = [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                    'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                    'fb_exchange_token' => $pageAccessShortLifeToken,
                ];
                $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $data['user_access_token']);
                $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();

                $pageFeed = $this->fb->get('/' . $data['page_id'] . '/feed?limit=10', $longTimeAccessToken['access_token']);
                $pageFeed = $pageFeed->getDecodedBody();

                foreach ($pageFeed['data'] as $postdetail) {

                    $postId = $postdetail['id'];
                    $postIds = $this->fb->get('/' . $postId . '?fields=id', $data['page_access_token']);
                    //  $postDetail = $this->fb->get('/' . '2274976966065498_2340631602833367' . '?fields=id,message,attachments{media,subattachments}', $data['page_access_token']);
                    $postIds = $postIds->getDecodedBody();

                    $postIdArray[] = $postIds['id'];
                }

                $weekly_array = [];
                $i = 1;
                $postresult = [];
                $currentDate = '';
                $time = '';
                foreach ($postIdArray as $postdata) {

                  //  echo $k, ' = ', $postdata, '<br />', PHP_EOL;

                    $postDetail = $this->fb->get('/' . $postdata . '?fields=id,message,created_time,permalink_url,attachments{media,subattachments,title},likes.summary(true),comments.summary(true)', $data['page_access_token']);
                    $postDetail = $postDetail->getDecodedBody();
                    !empty($postDetail['created_time']) ? $postDate = $postDetail['created_time'] : '';
                    if(!empty($postDate)) {
                        $carbon = new \Carbon\Carbon();
                        $date = $carbon->createFromTimestamp(strtotime($postDate),'EST');
                        $currentDate =  $date->format('Y-m-d');
                        $time =  $date->format('Y-m-d h:i:s');
                    }


                    $result['post_id'] = !empty($postDetail['id']) ? $postDetail['id'] : '';
                    $result['post_message'] = !empty($postDetail['message']) ? $postDetail['message'] : '';
                    $result['post_time'] = !empty($time) ? $time : '';
                    $result['post_url'] = !empty($postDetail['permalink_url']) ? $postDetail['permalink_url'] : '';
                    $result['post_likes'] = !empty($postDetail['likes']['summary']['total_count']) ? $postDetail['likes']['summary']['total_count'] : '';
                    $result['post_comments'] = !empty($postDetail['comments']['summary']['total_count']) ? $postDetail['comments']['summary']['total_count'] : '';
                    $result['post_image'] = !empty($postDetail['attachments']['data'][0]['media']['image']['src']) ? $postDetail['attachments']['data'][0]['media']['image']['src'] : '';
                    $result['post_multiple_images'] = !empty($postDetail['attachments']['data'][0]['subattachments']['data']) ? $postDetail['attachments']['data'][0]['subattachments']['data'] : '';
                    $result['post_video'] = !empty($postDetail['attachments']['data'][0]['media']['source']) ? $postDetail['attachments']['data'][0]['media']['source'] : '';

                    if (isset($data['screen']) && $data['screen'] == 'mobile') {
                        $postresult[] = $result;
                    }else{

                        if (in_array($currentDate, $weekly_array)) {
                            $postresult[$currentDate][] = $result;

                        } else {
                            $postresult[$currentDate][0] = $result;

                        }

                        array_push($weekly_array, $currentDate);
                        $result = [];
                    }
                }

                return $this->helpReturn('Post Feed Detail', $postresult);

            }


        } catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (Exception $e) {
            Log::info(" Main getSocialMediaPostFeed > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }

    }

    public function getSinglePost($request)
    {
        try {
            $businessObj = new BusinessEntity();

            $businessResult = $businessObj->userSelectedBusiness();

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];


            $data = [];
            $socialResult = SocialMediaMaster::select('business_id','type', 'name', 'access_token', 'page_access_token', 'page_id')
                ->where('type', '=','Facebook')
                ->where('business_id', $businessId)
                ->whereNotNull('page_access_token')
                ->whereNotNull('page_id')
                ->whereNotNull('access_token')
                ->first();

            if ($socialResult == null) {

                return $this->helpError(404, 'No Page found of this account.');
            }

            $data['page_access_token'] = $socialResult->page_access_token;
            $data['user_access_token'] = $socialResult->access_token;
            $data['page_id'] = $socialResult->page_id;
            $postresult  = [];
            $responseArray  = [];
            if ($data['user_access_token'] != '') {

                $pageAccessToken = $this->fb->get('/' . $data['page_id'] . '?fields=access_token', $data['user_access_token']);
                $pageAccessToken = $pageAccessToken->getDecodedBody();
                $pageAccessShortLifeToken = $pageAccessToken['access_token'];

                $params = [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                    'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                    'fb_exchange_token' => $pageAccessShortLifeToken,
                ];
                $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $data['user_access_token']);
                $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();
                $postId = $request->post_id;

                $pageFeed = $this->fb->get('/' . $postId, $longTimeAccessToken['access_token']);
                $pageFeed = $pageFeed->getDecodedBody();
                Log::info('facebook response');
                Log::info($pageFeed);
                $postDate = $pageFeed['created_time'];

                $date = strtotime($postDate);
                //$currentDate = date("d-M-y", $date);
                $time = date("Y-m-d h:i:s", $date);
                $responseArray = [
                    'created_at' => isset($time) && !empty($time) ? $time : '',
                    'message' => isset($pageFeed['message']) && !empty($pageFeed['message']) ? $pageFeed['message'] : '',
                    'id' => $pageFeed['id'],
                ];

                return $this->helpReturn('Get Single Post Successfully', $responseArray);

            }


        } catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (Exception $e) {
            Log::info(" getSinglePost > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
    }



    public function updateSinglePost($request)
    {
        try {
            $businessObj = new BusinessEntity();

            $businessResult = $businessObj->userSelectedBusiness();

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];

            $data = [];
            $socialResult = SocialMediaMaster::select('business_id','type', 'name', 'access_token', 'page_access_token', 'page_id')
                ->where('type', '=','Facebook')
                ->where('business_id', $businessId)
                ->whereNotNull('page_access_token')
                ->whereNotNull('page_id')
                ->whereNotNull('access_token')
                ->first();

            if ($socialResult == null) {

                return $this->helpError(404, 'No Page found of this account.');
            }

            $data['page_access_token'] = $socialResult->page_access_token;
            $data['user_access_token'] = $socialResult->access_token;
            $data['page_id'] = $socialResult->page_id;
            $postresult  = [];
            $responseArray  = [];
            if ($data['user_access_token'] != '') {

                $pageAccessToken = $this->fb->get('/' . $data['page_id'] . '?fields=access_token', $data['user_access_token']);
                $pageAccessToken = $pageAccessToken->getDecodedBody();
                $pageAccessShortLifeToken = $pageAccessToken['access_token'];

                $params = [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                    'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                    'fb_exchange_token' => $pageAccessShortLifeToken,
                ];
                $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $data['user_access_token']);
                $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();

                $postId = $request->post_id;

                $pageFeed = $this->fb->post('/' . $postId,['message' => $request->message], $longTimeAccessToken['access_token']);
                $pageFeed = $pageFeed->getDecodedBody();

                if(isset($pageFeed['success']) && !empty($pageFeed['success']) && $pageFeed['success'] == true){
                    return $this->helpReturn('Post Updated Successfully');
                }else{
                    return $this->helpError('404','Post Not Exist');
                }
            }


        } catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (Exception $e) {
            Log::info(" updateSinglePost > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
    }

    public function deleteSinglePost($request)
    {
        try {
            $businessObj = new BusinessEntity();

            $businessResult = $businessObj->userSelectedBusiness();

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];


            $data = [];
            $socialResult = SocialMediaMaster::select('business_id','type', 'name', 'access_token', 'page_access_token', 'page_id')
                ->where('type', '=','Facebook')
                ->where('business_id', $businessId)
                ->whereNotNull('page_access_token')
                ->whereNotNull('page_id')
                ->whereNotNull('access_token')
                ->first();

            if ($socialResult == null) {

                return $this->helpError(404, 'No Page found of this account.');
            }

            $data['page_access_token'] = $socialResult->page_access_token;
            $data['user_access_token'] = $socialResult->access_token;
            $data['page_id'] = $socialResult->page_id;
            $postresult  = [];
            $responseArray  = [];
            if ($data['user_access_token'] != '') {

                $pageAccessToken = $this->fb->get('/' . $data['page_id'] . '?fields=access_token', $data['user_access_token']);
                $pageAccessToken = $pageAccessToken->getDecodedBody();
                $pageAccessShortLifeToken = $pageAccessToken['access_token'];

                $params = [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => Config::get('apikeys.FACEBOOK_APP_ID'),
                    'client_secret' => Config::get('apikeys.FACEBOOK_APP_SECRET'),
                    'fb_exchange_token' => $pageAccessShortLifeToken,
                ];
                $longTimeAccessToken = $this->fb->post('/oauth/access_token', $params, $data['user_access_token']);
                $longTimeAccessToken = $longTimeAccessToken->getDecodedBody();
                $postId = $request->post_id;

                $pageFeed = $this->fb->delete('/' . $postId,[$postId], $longTimeAccessToken['access_token']);
                $pageFeed = $pageFeed->getDecodedBody();
                PostMaster::where('post_id',$postId)->where('social_media_type','Facebook')->delete();
                if(isset($pageFeed['success']) && !empty($pageFeed['success']) && $pageFeed['success'] == true){
                    return $this->helpReturn('Post Deleted Successfully');
                }else{
                    return $this->helpError('404','Post Not Exist');
                }

            }


        } catch (FacebookResponseException $e) {
            Log::info(" FacebookResponseException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookOtherException $e) {
            Log::info(" FacebookOtherException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (FacebookSDKException $e) {
            Log::info(" FacebookSDKException > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }

    }

}
