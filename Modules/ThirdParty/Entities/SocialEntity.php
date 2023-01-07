<?php

namespace Modules\ThirdParty\Entities;

use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use Modules\Business\Models\Business;
use Modules\Business\Entities\BusinessEntity;
use Modules\ThirdParty\Models\StatTracking;
use Modules\ThirdParty\Models\IssuesList;
use Modules\MadisonCentral\Entities\ChatHistoryEntity;
use Modules\MadisonCentral\Entities\CronJobEntity;
use Modules\ThirdParty\Models\PostMaster;
use Modules\ThirdParty\Models\PostMasterSocialMedia;
use Modules\ThirdParty\Models\SmediaPost;
use Modules\ThirdParty\Models\SocialMediaInsight;
use Modules\ThirdParty\Models\SocialMediaLike;

use Modules\ThirdParty\Models\SocialMediaMaster;

use Modules\ThirdParty\Services\Validations\AddSocialMediaValidator;

use Modules\ThirdParty\Models\UserIssues;

use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\TripAdvisorEntity;

use Modules\ThirdParty\Models\SMediaReview;
use Modules\ThirdParty\Entities\DashboardEntity;
use Carbon\Carbon;
use Modules\MadisonCentral\Models\ChatHistory;
use Modules\Business\Models\ChatMaster;
use Modules\Task\Models\BusinessTask;
use Request;
use DB;
use Config;
use Log;
use Exception;
use Storage;
use File;
use Modules\ThirdParty\Models\PostAttachment;
use Illuminate\Http\Response;
class SocialEntity extends AbstractEntity
{
    use UserAccess;

    protected $facebookEntity;

    protected $businessEntity;

    protected $thirdPartyEntity;

    protected $socialMediaMaster;
    protected $twitterEntity;

    protected $socialValidator;

    protected $data = [];

    public function __construct()
    {
        $this->facebookEntity = new FacebookEntity();

        $this->thirdPartyEntity = new TripAdvisorEntity();

        $this->socialMediaMaster = new SocialMediaMaster();
    }


    /**
     * add social business entries in table to
     * manual connect business later from online listing
     *
     * @param $requestbusi
     */
    public function socialModuleUpdate($request)
    {
        $socialData = IssuesList::where('module', 'Social Media')->select('site')->first();

        if ($socialData) {
            $socialData = $socialData->toArray();

            foreach ($socialData as $social) {

                $socialResult = SocialMediaMaster::where(
                    [
                        'business_id' => $request->get('business_id'),
                        'type' => $social
                    ]
                )->first();


                $first = DB::table('user_issues As us')
                    ->leftJoin('sys_issue As si', 'us.issue_id', 'si.issue_id')
                    ->leftJoin('social_media_master AS tpm', 'us.social_media_id', 'tpm.id')
                    ->select('si.site', 'tpm.name', 'us.id', 'us.business_id', 'user_id', 'us.issue_id', 'us.social_media_id', 'si.title', 'tpm.phone', 'tpm.website', 'tpm.street', 'tpm.city', 'tpm.zipcode', 'tpm.country', 'tpm.page_url')
                    ->where('us.business_id', $request->get('business_id'))
                    ->where('si.module', 'Social Media')
                    ->where('si.site', $social)
                    ->whereNull('us.third_party_id')->first();

                // if social type entry not found in user_issues & social_media_master table then create
                // a entry at social_media_master for manual connect in future.

                if (empty($socialResult) && empty($first)) {
                    $data['type'] = $social;
                    $data['business_id'] = $request->get('business_id');
                    SocialMediaMaster::create($data);
                } else {

                }

            }
        }
    }

    /**
     * This will update third party Social media pages against user Business
     * @param $request (token, business_id, access_token, page_id, type)
     * @param string $type
     * @return mixed|string
     */
    public function manageSocialBusinessPages($request, $type = 'all')
    {
        try {

            $businessEntityObj = new BusinessEntity();

            $type = strtolower($type);

            if ($type == 'facebook') {
                // get fb page details
                $result = $this->facebookEntity->getPageDetail($request);
            }
            $userId = '';

            return DB::transaction(function () use ($request, $result, $userId, $type, $businessEntityObj) {
                $responseCode = $result['_metadata']['outcomeCode'];
                $businessId = $request->get('business_id');
                $userAccesstoken = $request->get('access_token');

                if ($responseCode == 200) {
                    $records = $result['records'];
                    $dateFormat = 'Y-m-d';

                    if ($records) {
                        $data['business_id'] = $businessId;
                        $data['access_token'] = $userAccesstoken;
                        $data['type'] = ucfirst($type);
                        $data['page_id'] = getIndexedvalue($records, 'id');
                        $data['name'] = getIndexedvalue($records, 'name');
                        $data['page_url'] = getIndexedvalue($records, 'link');
                        $data['average_rating'] = getIndexedvalue($records, 'overall_star_rating');
                        $data['page_reviews_count'] = getIndexedvalue($records, 'recommendation_count');
                        $data['page_likes_count'] = getIndexedvalue($records, 'fan_count');
                        $data['website'] = getIndexedvalue($records, 'website');
                        $data['phone'] = getIndexedvalue($records, 'phone');
                        $data['street'] = !empty($records['location']['street']) ? $records['location']['street'] : '';
                        $data['city'] = !empty($records['location']['city']) ? $records['location']['city'] : '';
                        $data['zipcode'] = !empty($records['location']['zip']) ? $records['location']['zip'] : '';
                        $data['country'] = !empty($records['location']['country']) ? $records['location']['country'] : '';
                        $data['cover_photo'] = !empty($records['cover']) ? $records['cover']['source'] : '';
                        $data['add_review_url'] = $data['page_url'] . '/reviews/?ref=page_internal';
                        $data['page_access_token'] = !empty($records['long_life_access_token']['access_token']) ? $records['long_life_access_token']['access_token'] : '';

                        $data['is_manual_connected'] = 1;

                        // if is_silhouette == 1 then it has not any profile picture
                        if (!empty($records['picture']['data']['is_silhouette']) && $records['picture']['data']['is_silhouette'] == 1) {
                            $data['profile_photo'] = '';
                            $profilePicture = 0;
                        } else {
                            $data['profile_photo'] = !empty($records['picture']) ? $records['picture']['data']['url'] : '';
                            $profilePicture = 1;
                        }

                        $thirdPartyResult = SocialMediaMaster::where(
                            [
                                'business_id' => $businessId,
                                'type' => $data['type']
                            ]
                        )->first();


                        if (!empty($thirdPartyResult['id'])) {
                            $thirdPartyResult->update($data);

                            SMediaReview::where(

                                'social_media_id', $thirdPartyResult['id']

                            )->delete();


                            SocialMediaLike::where(

                                'social_media_id', $thirdPartyResult['id']

                            )->delete();


                        } else {
                            $thirdPartyResult = SocialMediaMaster::create($data);
                        }
                        $recommendationrecords = $result['records'];

                        $likesrecords = $result['records'];

                        $postrecords = $result['records'];

                        $pageviewsrecords = $result['records'];

                        $totalreachrecords = $result['records'];
                        $peopleengagedrecords = $result['records'];

                       /* Log::info("recor");
                        Log::info($recommendationrecords);
                        Log::info("recor 01");
                        Log::info($likesrecords);*/

                        if (!empty($recommendationrecords) && !empty($likesrecords)) {
                            Log::info("going to isnert");
                            $this->storeFacebookReview($recommendationrecords['page_recommendation_data']['data'], $thirdPartyResult['id'], $request);
                            $this->storeFacebookLikes($likesrecords['likes_data']['data'], $thirdPartyResult['id'], $request);
                        }

                        $thirdPartyId = $thirdPartyResult['id'];

                        return $this->helpReturn("Page successfully added..", $data);
                    }
                } else {

                    /**
                     * delete previous stored business trace from >> social_media_master
                     *  first business was present & second time business not found on update
                     * time, then delete the business.
                     */
                    return $this->helpError(404, 'Page Record not found.');
                }


            });
        } catch (Exception $exception) {
            Log::info(" manageSocialBusinessPages > " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. Please try again.');
        }
    }

    public function updateReviewRatingLikeCronJob($request)
    {
        try {
            $allBusiness = Business::select('business_id')->get()->toArray();

            $facebookPages = SocialMediaMaster::select('id', 'page_access_token', 'access_token','business_id','page_id', 'type')
                ->whereIn('business_id', $allBusiness)
                ->where('page_access_token','!=', 'Null')
                ->where('type','=', 'Facebook')
                ->get()->toArray();


            foreach ($facebookPages as $row) {
                $result = $this->facebookEntity->getPageReviewRatingInfo($row['page_access_token'], $row['page_id'], $row['type']);
                if ($result) {
                    if ($result['_metadata']['outcomeCode'] == 200) {
                        $page_url = $result['records']['page_url']['link'];

                        SocialMediaMaster::where('id', $row['id'])
                            ->update([
                                    'page_reviews_count' => !empty($result['records']['page_recommendation_count']) ? $result['records']['page_recommendation_count']: '',
                                    'page_likes_count' => !empty($result['records']['page_likes_count']['fan_count']) ? $result['records']['page_likes_count']['fan_count'] : '',
                                    'add_review_url' => $page_url . '/reviews/?ref=page_internal',
                                    'page_access_token' => $result['records']['long_life_access_token']['access_token']
                            ]
                        );
                    }
                }

            }
            return $this->helpReturn("Update Recommendation and Likes Cron Job Updated Now..");
        } catch (Exception $exception) {
            Log::info(" updateReviewRatingLikeCronJob > " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function getLastFacebookPost($request)
    {
        try {
            $userTypeArray = [
                [
                    'id' => 3,

                ],
                [
                    'id' => 4,

                ],
                [
                    'id' => 7,

                ],
                [
                    'id' => 8,

                ],
                [
                    'id' => 9,

                ]];

            $allBusiness = Business::select('business_id')->get()->toArray();

            $socialPages = DB::table('business_master as bm')
                ->join('social_media_master as tpm', 'bm.business_id', '=', 'tpm.business_id')
                ->join('user_master as usm', 'bm.user_id', '=', 'usm.id')
                ->where('page_id', '!=', '')
                ->where('page_access_token', '!=', '')
                ->where('page_url', '!=', '')
                ->whereIn('bm.business_id', $allBusiness)
                ->whereIn('usm.user_type', $userTypeArray)
                ->select('bm.user_id', 'usm.first_name', 'usm.company_name',  'usm.email', 'usm.user_type', 'usm.device_id', 'bm.business_id', 'bm.name as ThirdPartyBusinessName', 'tpm.id as third_party_id', 'page_id', 'page_access_token', 'type', 'page_url')
                ->orderby('tpm.business_id')
                ->get()->toArray();

            if (!empty($socialPages)) {
                $chatObj = new ChatHistoryEntity();

                $chatMessage = ChatMaster::select('message')->find(23);
                $message = $chatMessage['message'];

                $socialPages = json_decode(json_encode($socialPages), true);

                foreach ($socialPages as $row) {
                    $type = $row['type'];
                    $result = $this->facebookEntity->getPagePostInfo($row['page_access_token'], $row['page_id'], $type);

                    if ($result['_metadata']['outcomeCode'] == 200) {
                        if (!empty($result['records']['posts'][0]['created_time'])) {
                            $created_time = $result['records']['posts'][0]['created_time'];
                            $date_source = strtotime($created_time);
                            $FormatedPostDate = date('Y-m-d H:s:i', $date_source);
                            $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $FormatedPostDate);
                            $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', Carbon::now());
                            $diff_in_days = $to->diffInDays($from);

                            if ($diff_in_days > 3) {
                                $data = [
                                    'type' => $type,
                                    'third_party_id' => $row['third_party_id'],
                                    'notification' => '',
                                    'message' => $message,
                                    'page_url' => $row['page_url'],
                                    'email' => $row['email'],
                                    'first_name' => $row['first_name'],
                                    'company_name' => $row['company_name'],

                                ];
                                DB::transaction(function () use ($data, $row, $chatObj) {
                                    $notificationResponse = $chatObj->storeNotifications($data, 'content_research', $row['user_id']);

                                    $deviceToken = $row['device_id'];

                                    if ($deviceToken !== '' && $notificationResponse['_metadata']['outcomeCode'] == 200) {
                                        $recentNotificationRecord = $notificationResponse['records'];

                                        if (!empty($recentNotificationRecord)) {
                                            $cronApiManager = new CronJobEntity();
                                            $cronApiManager->pushNotificationTemplate($recentNotificationRecord, $data['message'], $deviceToken, $row['user_id']);
                                        }
                                    }
                                });
                            }

                        }
                    }
                }

                return $this->helpReturn('Social Pages Notification successfully sent.');
            }
        } catch (Exception $exception) {
            Log::info(" getLastFacebookPost > " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function getPageReviewsInsightCronJob($request)
    {
        try {
            $chatObj = new ChatHistoryEntity();

            $userTypeArray = [
                [
                    'id' => 3,

                ],
                [
                    'id' => 4,

                ],
                [
                    'id' => 7,

                ],
                [
                    'id' => 8,

                ],
                [
                    'id' => 9,

                ]];

            $allBusiness = Business::select('business_id')->get();

            $socialPages = DB::table('business_master as bm')
                ->join('social_media_master as tpm', 'bm.business_id', '=', 'tpm.business_id')
                ->join('user_master as usm', 'bm.user_id', '=', 'usm.id')
                ->where('page_id', '!=', '')
                ->where('page_access_token', '!=', '')
                ->where('page_url', '!=', '')
                //->where('bm.business_id', 407)
                ->whereIn('bm.business_id', $allBusiness)
                ->whereIn('usm.user_type', $userTypeArray)
                ->select('bm.user_id', 'usm.first_name', 'usm.email', 'usm.company_name','usm.device_id', 'bm.business_id', 'bm.name as ThirdPartyBusinessName', 'tpm.id', 'page_id', 'page_access_token', 'type', 'page_url')
                ->orderby('tpm.business_id')
                ->get()->toArray();

            if (!empty($socialPages)) {
                $socialPages = json_decode(json_encode($socialPages), true);

                foreach ($socialPages as $row) {
                    $request->request->add(['page_url' => $row['page_url'], 'page_id' => $row['page_id'], 'page_access_token' => $row['page_access_token']]);
                    $result = $this->facebookEntity->getPageReviewRatingLikeHistoricalData($request);

                    if ($result['_metadata']['outcomeCode'] == 200) {
                        $reviewsrecords = $result['records']['page_recommendation_data'];
                        $likesrecords = $result['records'];
                        $pagepostrecords = $result['records']['post_data']['data'];
                        $pageviewsrecords = $result['records']['page_views_data']['data'];
                        $totalreachrecords = $result['records']['total_reach_data']['data'];
                        $peopleengagedrecords = $result['records']['people_engaged_data']['data'];

                        if (!empty($reviewsrecords['data'])) {
                            DB::transaction(function () use ($reviewsrecords, $row, $request, $chatObj) {

                                $social_media_id = $row['id'];
                                $business_id = $row['business_id'];
                                foreach ($reviewsrecords['data'] as $row1) {

                                    $created_time = $row1['created_time'];
                                    $date_source = strtotime($created_time);
                                    $FormatedReviewDate = date('Y-m-d', $date_source);
                                    $reviewResult = [
                                        'social_media_id' => $social_media_id,
                                        'message' => !empty($row1['review_text']) ? $row1['review_text'] : '',
                                        'reviewer' => !empty($row1['reviewer']['name']) ? $row1['reviewer']['name'] : '',
                                        'rating' => !empty($row1['rating']) ? $row1['rating'] : '',
                                        'review_date' => $FormatedReviewDate,
                                    ];

                                    $existRecord = SMediaReview::where('message', $reviewResult['message'])
                                        ->where('reviewer', $reviewResult['reviewer'])
                                        ->where('review_date', $FormatedReviewDate)
                                        ->get()->toArray();

                                    if (empty($existRecord)) {

                                        $firstInsertId = SMediaReview::insertGetId($reviewResult);
                                        $firstIdArray[] = $firstInsertId;

                                        if ($reviewsrecords['data'] != 0 && $request != '') {
                                            $flag = 'review';
                                            $this->countFacebookReviewRating($social_media_id, $request, $flag);
                                        }
                                    }
                                }

                                if (isset($firstIdArray[0])) {
                                    $getId = $firstIdArray[0];
                                } else {
                                    $getId = 0;
                                }
                                if ($getId != 0) {
                                    $lastReviewsDetails = SMediaReview::select('reviewer')->where('review_id', '>=', $getId)->get();

                                    $data = [
                                        'type' => 'Facebook',
                                        'third_party_id' => $social_media_id,
                                        'notification' => $getId,
                                        'message' => $lastReviewsDetails[0]['reviewer'] . " recommended you "  . " on Facebook. Read full review now!",
                                        'page_url' => $row['page_url'],
                                        'email' => $row['email'],
                                        'first_name' => $row['first_name'],
                                        'company_name' => $row['company_name'],

                                    ];

                                    $notificationResponse = $chatObj->storeNotifications($data, 'Reviews', $row['user_id']);
                                    $deviceToken = $row['device_id'];
                                    if ($deviceToken !== '' && $notificationResponse['_metadata']['outcomeCode'] == 200) {
                                        $recentNotificationRecord = $notificationResponse['records'];

                                        if (!empty($recentNotificationRecord)) {
                                            $cronApiManager = new CronJobEntity();
                                            $cronApiManager->pushNotificationTemplate($recentNotificationRecord, $data['message'], $deviceToken, $row['user_id']);
                                        }
                                    }
                                }

                                $firstIdArray = null;
                            });
                        }

                        // social media insight cron job update
                        if (!empty($likesrecords['likes_data']['data'])) {
                            SocialMediaLike::where('social_media_id', $row['id'])->delete();
                            $flag = 'like';
                            $this->storeFacebookLikes($likesrecords['likes_data']['data'], $row['id'], $request, $flag);
                        }

                        if (!empty($pagepostrecords)) {
                            SocialMediaInsight::where('social_media_id', $row['id'])->delete();
                            $flag = 'pagepost';
                            $this->storeFacebookPagePost($pagepostrecords, $row['id'], $request, $flag);
                        }
                        if (!empty($pageviewsrecords)) {
                            $flag = 'pageviews';
                            $this->storeFacebookPageViews($pageviewsrecords, $row['id'], $request, $flag);
                        }
                        if (!empty($totalreachrecords)) {
                            $flag = 'totalreach';
                            $this->storeFacebookTotalReach($totalreachrecords, $row['id'], $request, $flag);
                        }
                        if (!empty($peopleengagedrecords)) {
                            // SocialMediaInsight::where('social_media_id', $row['id'])->delete();
                            $flag = 'peopleengage';
                            $this->storeFacebookPeopleEngaged($peopleengagedrecords, $row['id'], $request, $flag);
                        }

                    } else {
                        Log::info("No result found");
                    }
                }

                return $this->helpReturn("Reviews Likes Cron job Data is Updated");
            }

            return $this->helpError(404, 'No page found.');
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function storeFacebookLikes($data, $third_party_id, $request, $flag = '')
    {

        try {
            if ($data != 0 && $request != '') {
                foreach ($data[0]['values'] as $row) {


                    $created_time = $row['end_time'];
                    $date_source = strtotime($created_time);
                    $FormatedLikeDate = date('Y-m-d', $date_source);

                    $likesResult[] = [
                        'social_media_id' => $third_party_id,
                        'count' => $row['value'],
                        'like_date' => $FormatedLikeDate,
                    ];
                }

                if (!empty($likesResult)) {
                    SocialMediaLike::insert($likesResult);
                }
            }
            if ($data != 0 && $request != '') {
                $this->countFacebookReviewRating($third_party_id, $request, $flag);
            }
        }
        catch (Exception $exception) {
           Log::info('storeFacebookLikes.'. $exception->getMessage());
         //  return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function storeFacebookReview($data, $third_party_id, $request, $flag = '')
    {
        try
        {
            if ($data != 0){
                foreach ($data as $row) {
                    $created_time = $row['created_time'];
                    $date_source = strtotime($created_time);
                    $FormatedReviewDate = date('Y-m-d', $date_source);

                    $reviewResult[] = [
                        'social_media_id' => $third_party_id,
                        'message' => !empty($row['review_text']) ? $row['review_text'] : '',
                        // 'recommendation_type' => !empty($row['recommendation_type']) ? $row['recommendation_type'] : '',
                        'reviewer' => !empty($row['reviewer']['name']) ? $row['reviewer']['name'] : '',
                        'rating' => !empty($row['recommendation_type']) ? $row['recommendation_type'] : '',
                        'review_date' => $FormatedReviewDate,
                    ];
                }

                if (!empty($reviewResult)) {
                    SMediaReview::insert($reviewResult);
                }
            }

            if ($data != 0 && $request != '') {
                $dashboardObj = new DashboardEntity();
                $this->countFacebookReviewRating($third_party_id, $request, $flag);
                        }
        }catch (Exception $exception) {
            Log::info("storeFacebookReview " . $exception->getMessage());
        }
    }

    public function storeFacebookReviewForCronJob($data, $third_party_id, $request)
    {
        try {
            foreach ($data as $row) {

                $created_time = $row['created_time'];
                $date_source = strtotime($created_time);
                $FormatedReviewDate = date('Y-m-d', $date_source);

                $reviewResult = [
                    'social_media_id' => $third_party_id,
                    'message' => !empty($row['review_text']) ? $row['review_text'] : '',
                    'reviewer' => !empty($row['reviewer']['name']) ? $row['reviewer']['name'] : '',
                    'rating' => !empty($row['rating']) ? $row['rating'] : '',
                    'review_date' => $FormatedReviewDate,
                ];

                $existRecord = SMediaReview::where('message', $reviewResult['message'])
                    ->where('reviewer', $reviewResult['reviewer'])
                    ->where('rating', $reviewResult['rating'])
                    ->where('reviewer', $reviewResult['reviewer'])
                    ->get();

                if (empty($existRecord)) {
                    $smediaId= SMediaReview::insert($reviewResult);
                    }

                    if ($data != 0 && $request != '') {
                        $dashboardObj = new DashboardEntity();
                        $dashboardObj->countFacebookReviewRating($third_party_id, $request);
                    }


            }


        } catch
        (Exception $exception) {
            Log::info($exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }



    public function storeFacebookPagePost($data, $third_party_id, $request, $flag = '')
    {
        foreach ($data as $row) {

            $created_time = $row['created_time'];
            $date_source = strtotime($created_time);
            $FormatedPostDate = date('Y-m-d', $date_source);
            $postResult[] = [
                'social_media_id' => $third_party_id,
                'type' => 'Page Post',
                'count' => '1',
                'activity_date' => $FormatedPostDate,
            ];
        }


        if (!empty($postResult)) {
            SocialMediaInsight::insert($postResult);
        }

        if ($data != 0 && $request != '') {
            $dashboardObj = new DashboardEntity();

            $this->countFacebookReviewRating($third_party_id, $request, $flag);
            //$dashboardObj->countFacebookReviewRating($third_party_id,$request);
        }

    }


    public function storeFacebookPageViews($data, $third_party_id, $request, $flag = '')
    {
        foreach ($data[0]['values'] as $row) {
            $created_time = $row['end_time'];
            $date_source = strtotime($created_time);
            $FormatedPageViewDate = date('Y-m-d', $date_source);

            $pageViewResult[] = [
                'social_media_id' => $third_party_id,
                'type' => 'Page Views',
                'count' => $row['value'],
                'activity_date' => $FormatedPageViewDate,
            ];
        }


        if (!empty($pageViewResult)) {
            SocialMediaInsight::insert($pageViewResult);
        }

        if ($data != 0 && $request != '') {
            $dashboardObj = new DashboardEntity();
            //$dashboardObj->countFacebookReviewRating($third_party_id,$request);
            $this->countFacebookReviewRating($third_party_id, $request, $flag);
        }

    }

    public function storeFacebookTotalReach($data, $third_party_id, $request, $flag = '')
    {
        foreach ($data[0]['values'] as $row) {
            $created_time = $row['end_time'];
            $date_source = strtotime($created_time);
            $FormatedTotalReachDate = date('Y-m-d', $date_source);

            $totalReachResult[] = [
                'social_media_id' => $third_party_id,
                'type' => 'Total Reach',
                'count' => $row['value'],
                'activity_date' => $FormatedTotalReachDate,
            ];
        }

        if (!empty($totalReachResult)) {
            SocialMediaInsight::insert($totalReachResult);
        }

        if ($data != 0 && $request != '') {
            $dashboardObj = new DashboardEntity();
            $this->countFacebookReviewRating($third_party_id, $request, $flag);
            //$dashboardObj->countFacebookReviewRating($third_party_id,$request);
        }
    }

    public function storeFacebookPeopleEngaged($data, $third_party_id, $request, $flag = '')
    {
        foreach ($data[0]['values'] as $row) {
            $created_time = $row['end_time'];
            $date_source = strtotime($created_time);
            $FormatedPeopleEngagedDate = date('Y-m-d', $date_source);

            $PeopleEngagedResult[] = [
                'social_media_id' => $third_party_id,
                'type' => 'People Engaged',
                'count' => $row['value'],
                'activity_date' => $FormatedPeopleEngagedDate,
            ];
        }


        if (!empty($PeopleEngagedResult)) {
            SocialMediaInsight::insert($PeopleEngagedResult);
        }

        if ($data != 0 && $request != '') {
            $dashboardObj = new DashboardEntity();
            //$dashboardObj->countFacebookReviewRating($third_party_id,$request);
            $this->countFacebookReviewRating($third_party_id, $request, $flag = '');
        }

    }

    function hasThrottlingLimit($user, $execLimit = 1)
    {

        /******************** Throttle control using file ************************/
        //$dir = Config::get('store.abs_throttling_control_folder');
        $dir = public_path() . '/limit/';
        $keyFile = "{$dir}{$user->id}.key";

        /* Attempts to create the directory if dosent exists */
        if (!file_exists("{$dir}") && !mkdir($dir, 0777, TRUE)) {
            throw new \Exception("Internal server error. Please contact with server administrator");
        }

        /* Attempts to create the default store key file if dosent exists */
        $defaultStructure = json_encode(['time' => time(), 'count' => 0]);
        if (!file_exists($keyFile) && !file_put_contents($keyFile, $defaultStructure)) {
            throw new \Exception("Internal server error. Please contact with server administrator");
        }

        $fp = fopen($keyFile, "r+");
        while (TRUE) {
            if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock
                try {
                    $data = json_decode(file_get_contents($keyFile));

                    if (empty($data)) {
                        $data = json_decode($defaultStructure);
                    }
                } catch (\Exception $e) {
                    $data = json_decode($defaultStructure);
                }

                $canExecute = false;
                // if we're in the current second
                if ((time() - $data->time) == 0) {
                    if ($data->count < $execLimit) $canExecute = TRUE;
                } else { // if we're in next second and we'll execute under quota limit
                    $data->count = 0;
                    $data->time = time();
                    $canExecute = TRUE;
                }


                if ($canExecute) {
                    $data->count += 1;
                    ftruncate($fp, 0);      // truncate file
                    rewind($fp);
                    fwrite($fp, json_encode($data));
                    fflush($fp);            // flush output before releasing the lock
                    flock($fp, LOCK_UN);    // release the lock
                    break;//successful execution stop the lock
                } else {
                    flock($fp, LOCK_UN);    // release the lock
                }

            }
            //      nth     ms
            usleep(1000 * 500);
        }
        fclose($fp);
        /******************** Throttle control using file ************************/
    }


    public function updateSocialMediaIssuesTask($request)
    {
        try {
            $allBusiness = Business::select('business_id')->get();

            $SocialMediaData = DB::table('business_master as bm')
                ->join('social_media_master as sm', 'bm.business_id', '=', 'sm.business_id')
                ->join('user_master as usm', 'bm.user_id', '=', 'usm.id')
                //->where('bm.business_id', 48)
                ->whereIn('bm.business_id', $allBusiness)
                ->where('bm.business_profile_status', 'completed')
                ->select('bm.user_id', 'bm.business_id', 'bm.name as SocialMediaBusinessName', 'sm.id', 'page_id', 'page_access_token', 'sm.phone', 'sm.street', 'sm.website', 'sm.cover_photo', 'sm.profile_photo', 'type')
                ->orderby('sm.business_id')
                ->get()->toArray();


            if (!empty($SocialMediaData)) {
                foreach ($SocialMediaData as $row) {

                    $result = $this->facebookEntity->getPageBasicInfo($row->page_access_token, $row->page_id, $row->type);

                    if ($result['_metadata']['outcomeCode'] == 200) {
                        $records = $result['records'];

                        $phone = !empty($records['phone']) ? $records['phone'] : '';
                        $address = !empty($records['location']) ? $records['location']['street'] : '';
                        $website = !empty($records['website']) ? $records['website'] : '';
                        $coverPhoto = !empty($records['cover']) ? $records['cover']['source'] : '';

                        if (!empty($records['picture']['data']['is_silhouette']) && $records['picture']['data']['is_silhouette'] == 1) {
                            $data['profile_photo'] = '';
                            $profilePicture = 0;
                        } else {
                            $data['profile_photo'] = !empty($records['picture']) ? $records['picture']['data']['url'] : '';
                            $profilePicture = 1;
                        }


                        SocialMediaMaster::where('business_id', $row->business_id)
                            ->where('type', $row->type)
                            ->update(['phone' => $phone,
                                'street' => $address,
                                'website' => $website,
                                'profile_photo' => $profilePicture,
                                'cover_photo' => $coverPhoto,
                            ]);


                        $issueData = [
                            [
                                'key' => 'phone',
                                'value' => $phone,
                                'issue' => ($phone != '') ? 18 : 49,
                                'oldIssue' => ($phone == '') ? 18 : 49
                            ],
                            [
                                'key' => 'address',
                                'value' => $address,
                                'issue' => ($address != '') ? 19 : 51,
                                'oldIssue' => ($address == '') ? 19 : 51
                            ],
                            [
                                'key' => 'website',
                                'value' => $website,
                                'issue' => ($website != '') ? 20 : 50,
                                'oldIssue' => ($website == '') ? 20 : 50
                            ],
                            [
                                'key' => 'profile_photo',
                                'value' => $profilePicture,
                                'issue' => 22,
                            ],
                            [
                                'key' => 'cover_photo',
                                'value' => $coverPhoto,
                                'issue' => 23,
                            ]
                        ];

                        $thirdObj = new ThirdPartyEntity();

                        $thirdObj->globalIssueGenerator($row->user_id, $row->business_id, $row->id, $issueData, $row->type, 'social');

                    }
                    return $this->helpReturn("Social Media Issues/Task Updated");
                }
            }

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }

    }


    public function countFacebookReviewRating($third_party_id, $request, $flag = '')
    {

        try {
            if (!empty($request->token) && !empty($third_party_id)) { // for authenticated user
                $businessObj = new BusinessEntity();

                $businessResult = $businessObj->userSelectedBusiness($request);
                $businessId = $businessResult['records']['business_id'];
                if (empty($businessId)) {
                    return $this->helpReturn("Business Not Exist.");
                }
                $socialMediaId = SocialMediaMaster::select('id')
                    ->where('business_id', $businessId)
                    ->first();
                $socialMediaId = $socialMediaId->id;


            } else { //for cron job
                $socialMediaId = $third_party_id;
            }

            $currentDate = Carbon::now();
            $FormatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');


            $historical_reviews = SMediaReview::where('social_media_id', $socialMediaId)//Query for historical reivew
            ->where(DB::raw("STR_TO_DATE(`review_date`, '%Y-%m-%d')"), '<=', $FormatedCurrentDate)
                ->select('social_media_id', 'review_date', DB::raw('count(review_date) as total'))
                ->groupBy('review_date', 'social_media_id')->get()->toArray();

            if (empty($flag)) {
                if (empty($historical_reviews)) {
                   // return $this->helpReturn("No Review Found.");
                }
            }


                $dateFormat = dateFormatUsing();
            if (count($historical_reviews) > 0){
                foreach ($historical_reviews as $review) {
                    $reviewDate = getFormattedDate($review['review_date']);
                    $appendReviewArray[] = [
                        'social_media_id' => $review['social_media_id'],
                        'activity_date' => $reviewDate,
                        'site_type' => 'Facebook',
                        'count' => $review['total'],
                        'type' => 'RV',
                    ];
                }
            }else{
                $appendReviewArray[] = [];
            }




            /**************************Likes Section***************************/

            $historical_likes = SocialMediaLike::where('social_media_id', $socialMediaId)//Query for historical review
            ->where('like_date', '<=', $FormatedCurrentDate)
                ->select('social_media_id', 'like_date', DB::raw('sum(count) as total'))
                ->groupBy('like_date', 'social_media_id')->get()->toArray();

            if (empty($flag)) {
                if (empty($historical_likes)) {
                  //  return $this->helpReturn("No Likes Found.");
                }
            }

            if (count($historical_likes) > 0){
                $dateFormat = dateFormatUsing();
                foreach ($historical_likes as $likes) {
                    $likesDate = getFormattedDate($likes['like_date']);
                    $appendLikesArray[] = [
                        'social_media_id' => $likes['social_media_id'],
                        'activity_date' => $likesDate,
                        'site_type' => 'Facebook',
                        'count' => $likes['total'],
                        'type' => 'LK',
                    ];
                }
            }else{
                $appendLikesArray[] = [];
            }


            //   echo 'after like';
//            /**************************POST Section***************************/
//            $historical_posts = SocialMediaInsight::where('social_media_id', $socialMediaId)//Query for historical review
//            ->where('activity_date', '<=', $FormatedCurrentDate)
//                ->where('type', '=', 'Page Post')
//                ->select('social_media_id', 'activity_date', DB::raw('sum(count) as total'))
//                ->groupBy('activity_date', 'social_media_id')->get()->toArray();
//
//            if (empty($flag)) {
//                if (empty($historical_posts)) {
//                    return $this->helpReturn("No Post Found.");
//                }
//            }
//            $dateFormat = dateFormatUsing();
//
//            foreach ($historical_posts as $posts) {
//
//                $postsDate = getFormattedDate($posts['activity_date']);
//                $appendPostsArray[] = [
//                    'social_media_id' => $posts['social_media_id'],
//                    'activity_date' => $postsDate,
//                    'site_type' => 'Facebook',
//                    'count' => $posts['total'],
//                    'type' => 'FP',
//                ];
//            }
//
//            /**************************Page Views Section***************************/
//            $historical_page_views = SocialMediaInsight::where('social_media_id', $socialMediaId)//Query for historical review
//            ->where('activity_date', '<=', $FormatedCurrentDate)
//                ->where('type', '=', 'Page Views')
//                ->select('social_media_id', 'activity_date', DB::raw('sum(count) as total'))
//                ->groupBy('activity_date', 'social_media_id')->get()->toArray();
//
//            if (empty($flag)) {
//                if (empty($historical_page_views)) {
//                    return $this->helpReturn("No Page View Found.");
//                }
//            }
//
//            $dateFormat = dateFormatUsing();
//
//            foreach ($historical_page_views as $pageview) {
//
//                $pageViewsDate = getFormattedDate($pageview['activity_date']);
//                $appendPageViewsArray[] = [
//                    'social_media_id' => $pageview['social_media_id'],
//                    'activity_date' => $pageViewsDate,
//                    'site_type' => 'Facebook',
//                    'count' => $pageview['total'],
//                    'type' => 'PA',
//                ];
//            }
//
//            /**************************Total Reach Section***************************/
//            $historical_page_total_reach = SocialMediaInsight::where('social_media_id', $socialMediaId)//Query for historical review
//            ->where('activity_date', '<=', $FormatedCurrentDate)
//                ->where('type', '=', 'Total Reach')
//                ->select('social_media_id', 'activity_date', DB::raw('sum(count) as total'))
//                ->groupBy('activity_date', 'social_media_id')->get()->toArray();
//
//            if (empty($flag)) {
//                if (empty($historical_page_total_reach)) {
//                    return $this->helpReturn("No Total Reach Found.");
//                }
//            }
//
//            $dateFormat = dateFormatUsing();
//
//
//            foreach ($historical_page_total_reach as $totalreach) {
//
//                $totalReachDate = getFormattedDate($totalreach['activity_date']);
//                $appendPageTotalReachArray[] = [
//                    'social_media_id' => $totalreach['social_media_id'],
//                    'activity_date' => $totalReachDate,
//                    'site_type' => 'Facebook',
//                    'count' => $totalreach['total'],
//                    'type' => 'TR',
//                ];
//            }
//
//            /**************************People Engaged Section***************************/
//            $historical_page_people_engaged = SocialMediaInsight::where('social_media_id', $socialMediaId)//Query for historical review
//            ->where('activity_date', '<=', $FormatedCurrentDate)
//                ->where('type', '=', 'People Engaged')
//                ->select('social_media_id', 'activity_date', DB::raw('sum(count) as total'))
//                ->groupBy('activity_date', 'social_media_id')->get()->toArray();
//
//            if (empty($flag)) {
//                if (empty($historical_page_people_engaged)) {
//                    return $this->helpReturn("No People Engaged Found.");
//                }
//            }
//
//            $dateFormat = dateFormatUsing();
//
//            foreach ($historical_page_people_engaged as $peopleengaged) {
//
//                $peopleEngagedDate = getFormattedDate($peopleengaged['activity_date']);
//                $appendPeopleEngagedArray[] = [
//                    'social_media_id' => $peopleengaged['social_media_id'],
//                    'activity_date' => $peopleEngagedDate,
//                    'site_type' => 'Facebook',
//                    'count' => $peopleengaged['total'],
//                    'type' => 'PE',
//                ];
//            }


            if (empty($flag)) {
                StatTracking::where('social_media_id', $socialMediaId)->delete();
                StatTracking::insert($appendReviewArray); //for  Review
                StatTracking::insert($appendLikesArray); //for  Likes
//                StatTracking::insert($appendPostsArray); //for  Posts
//                StatTracking::insert($appendPageViewsArray); //for  Page Views
//                StatTracking::insert($appendPageTotalReachArray); //for Page Total Reach
//                StatTracking::insert($appendPeopleEngagedArray); //for Page People Engaged
                $finalArray[] = [
                    'reviews' => $appendReviewArray,
                    'likes' => $appendLikesArray,
//                    'posts' => $appendPostsArray,
//                    'views' => $appendPageViewsArray,
//                    'total reach' => $appendPageTotalReachArray,
//                    'people engaged' => $appendPeopleEngagedArray,
                ];
            } else if ($flag == 'review') {
                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'RV')->delete();
                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'RG')->delete();
                StatTracking::insert($appendReviewArray); //for  Review
          //      StatTracking::insert($appendRatingArray); //for  Rating

//            } else if ($flag == 'pagepost') {
//                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'FP')->delete();
//                StatTracking::insert($appendPostsArray); //for  Review
//
//            } else if ($flag == 'pageviews') {
//                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'PA')->delete();
//                StatTracking::insert($appendPageViewsArray); //for  Review
//
//            } else if ($flag == 'totalreach') {
//                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'TR')->delete();
//                StatTracking::insert($appendPageTotalReachArray); //for  Review
//            } else if ($flag == 'peopleengage') {
//                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'PE')->delete();
//                StatTracking::insert($appendPeopleEngagedArray); //for  Review
            } else if ($flag == 'like') {
                StatTracking::where('social_media_id', $socialMediaId)->where('type', '=', 'LK')->delete();
                StatTracking::insert($appendLikesArray); //for  LIKE
            }

            return $this->helpReturn("Get Stats Count Update Cron Job.", $finalArray);
        } catch (Exception $e) {
            Log::info("countFacebookReviewRating " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }
    }

    public function getSocialMediaPosts($data)
    {
        try {
            $businessResult = $data['businessResult'];

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $businessId = $businessResult['records']['business_id'];
            $appendArray = [];
            $finalArray = [];
            $postAppend = [];
            $connectedArray = [];
            $notConnectedArray = [];
            $connectionArray = [];

            $socialMedia = SocialMediaMaster::where('business_id', $businessId)
                ->where('access_token', '!=', '')
                ->get()->toArray();

            $list = 'main';
            if(!empty($data['social_module_list']))
            {
                $list = 'all';
            }

            if (empty($socialMedia)) {
                foreach (moduleSocialList($list) as $key) {
                    $notConnectedArray[$key] = [
                        'status' => 'not_connected',
                        'posts' => [],
                    ];
                }

                return $this->helpError(404, 'No Social Connect.', $notConnectedArray);
            }

            $types = array_column($socialMedia, 'type');

            foreach ($socialMedia as $type) {

                $connectedArray[$type['type']] = [
                    'status' => 'connected',
                    'name' => !empty($type['name']) ? $type['name'] : '',
                    'website' => !empty($type['website']) ? $type['website'] : '',
                    'page_url' => !empty($type['page_url']) ? $type['page_url'] : '',
                    'profile_photo' => !empty($type['profile_photo']) ? $type['profile_photo'] : '',
                    'city' => !empty($type['city']) ? $type['city'] : '',
                    'street' => !empty($type['street']) ? $type['street'] : '',
                    'country' => !empty($type['country']) ? $type['country'] : '',
                    'created_at' => !empty($type['created_at']) ? $type['created_at'] : '',
                    'posts' => [],
                ];
            }

            $typesDiff = array_diff(moduleSocialList($list), $types);

            foreach ($typesDiff as $type) {
                $notConnectedArray[$type] = [
                    'status' => 'not_connected',
                    'posts' => [],
                ];
            }

            $connectionArray = array_merge($notConnectedArray, $connectedArray);

            if (empty($data['status'])) {

                return $this->helpReturn("Get Posts Data Successfully.", $connectionArray);

            }

            $statusType = '';

            foreach ($data['status'] as $status) {

                if ($status == 'draft' || $status == 'schedule') {

                    if (isset($data['screen']) && $data['screen'] == 'mobile') {
                        $posts = PostMaster::where('status', $status)
                            ->where('business_id', $businessId)->orderBy('created_at', 'DESC')
                            ->with('postMasterSocialMedia')->with('attachment')
                            ->get()
                            ->toArray();
                    } else {
                        $posts = PostMaster::where('status', $status)
                            ->where('business_id', $businessId)->orderBy('created_at', 'DESC')
                            ->with('postMasterSocialMedia')->with('attachment')
                            ->get()->groupBy(function ($item) {
                                $carbon = new \Carbon\Carbon();
                                $date = $carbon->createFromTimestamp(strtotime($item->created_at));
                                return $date->format('Y-m-d');
                                //return $item->created_at->format('d-M-y');
                                //return $item->created_at;
                            })
                            ->toArray();
                    }
                    $appendArray[$status] = $posts;
                } else {
                    $statusType = $status;
                }
            }

            $posts = [];

            if (isset($statusType) && $statusType == 'published') {
                $data['businessId'] = $businessId;

                if (!empty($socialMedia)) {
                    foreach ($socialMedia as $social) {

                        $posts = [];
                        if ($social['type'] == 'Facebook') {
                            $response = $this->facebookEntity->getSocialMediaPostFeed($data);
                            if ($response['_metadata']['outcomeCode'] == 200) {
                                $posts = $response['records'];
                            }

                        } else if ($social['type'] == 'Twitter') {
                            $response = $this->twitterEntity->getAllPublishedPost($data);

                            if ($response['_metadata']['outcomeCode'] == 200) {
                                $posts = $response['records'];
                            }
                        }

                        $condition = $socialMedia != null ? 'connected' : 'not_connected';

                        Log::info("ty " . $social['type']);

                        $appendArray['published'][$social['type']] = [
                            'posts' => $posts,
                            'status' => $socialMedia != null ? 'connected' : 'not_connected',
                        ];

                        $postAppend = [];
                        $posts = [];
                    }
                }
            }

            $finalArray = array_merge($appendArray, $connectionArray);

            return $this->helpReturn("Get Posts Data Successfully.", $finalArray);

        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happend');
        }
    }


    public function addPost($request)
    {
        try {
            Log::info("addPost > " );
            Log::info($request->attach_file);

            $businessObj = new BusinessEntity();


            $businessResult = $businessObj->userSelectedBusiness();

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];

            $url = '';
            $media = '';
            $appendArray = [];
            $urlAppend = [];
            $mediaIds = [];
            $appendTypes = [];

            if (!$this->socialValidator->with($request->all())->passes()) {
                return $this->helpError(2, $this->socialValidator->errors());
            }
            $postId = isset($request->post_id) && !empty($request->post_id) ? $request->post_id : '';


            /**
             * Check Media Exist image or Video
             */
            if ($request->hasFile('attach_file')) {
                $attachedFile = $request->attach_file;
                $i = 0;
                Log::info('check file content');
//                Log::info($request->file);
                foreach ($attachedFile as $file) {


                    Log::info("01A");
                    Log::info($file);
                    Log::info("01B");
                    Log::info($attachedFile);

                    Log::info("01C");
                    Log::info($attachedFile[$i]);

                    $file = $attachedFile[$i];
                    $extension = $file->getClientOriginalExtension();

                    Log::info("01D");
                    Log::info($extension);

                    $file = $attachedFile[$i];
                    $extension = $file->getClientOriginalExtension();
                    $file_size = $file->getSize();
                    $file_size = number_format($file_size / 1048576, 2);

                    if ($extension == 'jpeg' || $extension == 'jpg' || $extension == 'JPEG' || $extension == 'png' || $extension == 'PNG') {
                        $request->request->add(['media_type' => 'image']);
                    } else if ($extension == 'mp4') {
                        $request->request->add(['media_type' => 'video']);
                    }

                    $fileName = $file->getFilename() . time() . '.' . $extension;
                    Log::info('sleep');
                    sleep(1);
                    Log::info('sleep end');
                    Storage::disk('local')->put($fileName, File::get($file));

                    $url = Storage::url($fileName);
                    $urlAppend[] = [
                        'media_url' => $url,
                        'ext' => $extension,
                        'size' => $file_size,
                        'type' => $request->media_type,
                    ];
                    $i++;
                }

            }

            $request->request->add(['business_id' => $businessId]);

            if (isset($postId) && !empty($postId) && ($request->status == 'draft' || $request->status == 'schedule')) {
                Log::info($postId);
                Log::info('Draft to Schedule');
                Log::info('Schedule to Draft');
                if ($request->status == 'draft') {
                    $post = PostMaster::where('id', $postId)->update(['message' => $request->message, 'status' => $request->status, 'schedule' => null]);
                } else {
                    $post = PostMaster::where('id', $postId)->update(['message' => $request->message, 'status' => $request->status, 'schedule' => $request->schedule_date]);
                }

                if (isset($request['deleted_files'][0]) && !empty($request['deleted_files'][0])) {
                    $deleteAttachment = PostAttachment::where('post_master_id', $postId)->whereIn('media_url', $request['deleted_files'])->delete();
                    foreach ($request['deleted_files'] as $row) {
                        $pieces = explode('/', $row);
                        Storage::disk('local')->delete($pieces[1]);
                    }

                }
                if (isset($urlAppend) && !empty($urlAppend)) {
                    foreach ($urlAppend as $row) {
                        $appendArray[] = [
                            'media_url' => $row['media_url'],
                            'post_master_id' => $postId,
                            'type' => $row['type'],
                            'size' => $row['size'],

                        ];
                    }
                    PostAttachment::insert($appendArray);
                }

                PostMasterSocialMedia::where('post_master_id', $postId)->delete();

                foreach ($request['details'] as $row) {
                    $appendTypes[] = [
                        'social_media_type' => $row['type'],
                        'post_master_id' => $postId,
                        'business_id' => $businessId
                    ];
                }
                Log::info('types');
                Log::info($appendTypes);
                PostMasterSocialMedia::insert($appendTypes);

            }
            else if ($request->status == 'draft' || $request->status == 'schedule') {
                /**
                 * Direct Draft Or Schedule Case
                 */

                $post = PostMaster::create(['business_id' => $businessId, 'message' => $request->message, 'status' => $request->status, 'schedule' => $request->schedule_date]);

                if (!empty($urlAppend)) {
                    Log::info('in loop');
                    Log::info($urlAppend);
                    foreach ($urlAppend as $row) {
                        Log::info('in row');
                        Log::info($row);
                        $appendArray[] = [
                            'media_url' => $row['media_url'],
                            'post_master_id' => $post->id,
                            'type' => $row['type'],
                            'size' => $row['size'],
                            'ext' => $row['ext'],

                        ];
                    }
                    PostAttachment::insert($appendArray);
                }
                foreach ($request['details'] as $row) {
                    $appendTypes[] = [
                        'social_media_type' => $row['type'],
                        'post_master_id' => $post->id,
                        'business_id' => $businessId,
                    ];
                }
                PostMasterSocialMedia::insert($appendTypes);
            }

            else if ($request->status == 'published' && empty($postId)) {
                foreach ($request['details'] as $row) {
                    if ($row == 'Facebook') {
                        Log::info('in facebook');
                        $this->facebookEntity->directPublishedPost($request);
                    } else if ($row == 'Twitter') {
                        Log::info('in Twitter');
                        $this->twitterEntity->directPublishedPost($request);

                    }
                }
            }
            else if ($request->status == 'published' && isset($postId)) {

                if (isset($request['deleted_files'][0]) && !empty($request['deleted_files'][0])) {
                    $deleteAttachment = PostAttachment::whereIn('media_url', $request['deleted_files'])->delete();
                    foreach ($request['deleted_files'] as $row) {
                        $pieces = explode('/', $row);
                        Storage::disk('local')->delete($pieces[1]);
                    }

                }
                if (!empty($urlAppend)) {
                    foreach ($urlAppend as $row) {
                        $appendArray[] = [
                            'media_url' => $row['media_url'],
                            'post_master_id' => $request->post_id,
                            'type' => $row['type'],
                            'size' => '',

                        ];
                    }
                    PostAttachment::insert($appendArray);
                }

                $attachment = PostAttachment::select('media_url', 'post_master_id', 'type', 'size')->where('post_master_id', $request->post_id)->get()->toArray();

                $request->request->add(['business_id' => $businessId, 'urls' => $attachment]);

                foreach ($request['details'] as $row) {
                    if ($row == 'Facebook') {
                        $response = $this->facebookEntity->indirectPublishedPost($request);
                        if (isset($response['_metadata']['outcomeCode']) && $response['_metadata']['outcomeCode'] == 200) {
                            $result[] = $response['records'];
                        }

                    } else if ($row == 'Twitter') {
                        Log::info('in Twitter');
                        $response = $this->twitterEntity->indirectPublishedPost($request);
                        if (isset($response['_metadata']['outcomeCode']) && $response['_metadata']['outcomeCode'] == 200) {
                            $result[] = $response['records'];
                        }

                    }
                }

                PostMaster::insert($result);
                if (!empty($attachment)) {
                    foreach ($attachment as $row) {
                        $pieces = explode('/', $row['media_url']);
                        Storage::disk('local')->delete($pieces[1]);
                    }
                }
                PostMaster::where('id', $request->post_id)
                    ->where('business_id', $businessId)
                    ->whereNull('post_id')->delete();

            }
            if($request->status == 'draft'){
                return $this->helpReturn("Post successfully saved as a draft.", $appendArray);
            }else{
                return $this->helpReturn("Post Added Successfully.", $appendArray);
            }

        } catch (Exception $e) {
            Log::info(" addPost > " . $e->getMessage() . ' > ' . $e->getLine());
            return $this->helpError(1, 'Some Problem happend');
        }
    }


    public function getSocialMediaPost($request)
    {
        try {
            $businessObj = new BusinessEntity();
            $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();
            // user is not found.
            if ($checkPoint['_metadata']['outcomeCode'] != 200) {
                return $checkPoint;
            }
            $user = $checkPoint['records'];
            $businessResult = $businessObj->userSelectedBusiness($user);
            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user busienss.');
            }
            $businessId = $businessResult['records']['business_id'];

            $post = PostMaster::where('id', $request->post_id)
                ->where('business_id', $businessId)
                ->with('postMasterSocialMedia')->with('attachment')->first();

            return $this->helpReturn("Get Post Successfully.", $post);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happend');
        }


    }


    public function deletePost($request)
    {
        try {
            $businessObj = new BusinessEntity();
            $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();
            // user is not found.
            if ($checkPoint['_metadata']['outcomeCode'] != 200) {
                return $checkPoint;
            }
            $user = $checkPoint['records'];
            $businessResult = $businessObj->userSelectedBusiness($user);
            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user busienss.');
            }
            $businessId = $businessResult['records']['business_id'];

            PostMaster::where('id', $request->post_id)
                ->where('business_id', $businessId)
                ->delete();

            return $this->helpReturn("Post Delete Successfully.");
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happend');
        }
    }


    public function cronJobSchedulePost($request)
    {
        try {
            $business = Business::select('business_id')->get()->toArray();
            //$business = Business::select('business_id')->where('business_id', '=', 48)->get()->toArray();
            $timezoneDate = Carbon::now(new \DateTimeZone('EST'));


            $formatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $timezoneDate)->format('Y-m-d H:i:s');
            Log::info($formatedCurrentDate);
            $posts = '';
            $result = [];
            $posts = PostMaster::whereIn('business_id', $business)
                ->where('status', 'schedule')
                ->where('schedule', '<=', $formatedCurrentDate)
                ->get()->toArray();

            if (!empty($posts)) {
                foreach ($posts as $post) {

                    $socials = PostMasterSocialMedia::select('social_media_type')->where('post_master_id', $post['id'])->get()->toArray();
                    $attachment = PostAttachment::select('media_url', 'post_master_id', 'type', 'size')->where('post_master_id', $post['id'])->get()->toArray();
                    $request->request->add(['post_id' => $post['id'], 'status' => 'published', 'business_id' => $post['business_id'], 'message' => $post['message'], 'urls' => $attachment]);
                    foreach ($socials as $row) {
                        if ($row['social_media_type'] == 'Facebook') {
                            Log::info('in facebook');
                            $response = $this->facebookEntity->indirectPublishedPost($request);
                            if (isset($response['_metadata']['outcomeCode']) && $response['_metadata']['outcomeCode'] == 200) {
                                $result[] = $response['records'];
                            }

                        } else if ($row['social_media_type'] == 'Twitter') {
                            Log::info('in Twitter');
                            $response = $this->twitterEntity->indirectPublishedPost($request);
                            if (isset($response['_metadata']['outcomeCode']) && $response['_metadata']['outcomeCode'] == 200) {
                                $result[] = $response['records'];
                            }

                        } else if ($row['social_media_type'] == 'Linkedin') {
                            Log::info('in Linkedin');
                            $response = $this->linkedinEntity->indirectPublishedPost($request);
                            if (isset($response['_metadata']['outcomeCode']) && $response['_metadata']['outcomeCode'] == 200) {
                                $result[] = $response['records'];
                                Log::info('inserted array result');
                                Log::info($result);
                            }
                        }
                    }
                    Log::info('before insert result');
                    Log::info($result);
                    if (!empty($result)) {

                        PostMaster::insert($result);
                        if (!empty($attachment)) {
                            foreach ($attachment as $row) {
                                $pieces = explode('/', $row['media_url']);
                                Storage::disk('local')->delete($pieces[1]);
                            }
                        }
                        PostMaster::where('id', $request->post_id)
                            ->where('business_id', $post['business_id'])
                            ->whereNull('post_id')->delete();
                    }
                }
            }

            return $this->helpReturn("Cron Job Run Successfully.", $posts);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happend');
        }
    }

    public function removeThirdParties($request)
    {
        try {
            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $type = $request->type;
            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userId = $businessResult['user_id'];

            Log::info("type " . $request->type);
            Log::info("businessId $businessId ");

            if ($type != 'Facebook') {
                SocialMediaMaster::where('business_id', $businessId)
                    ->where('type', $type)
                    ->delete();
            }

            $msg = $type . ' Deleted Successfully';

            return $this->helpReturn($msg);
        } catch (Exception $e) {
            Log::info(" removeThirdParties > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happend. Please try again later.');
        }
    }
    //get social images function
    public function getimages($request)
    {
        try {
            $file = Storage::disk('local')->get($request->name);

            return (new Response($file, 200))->header('Content-Type', ['image/jpg','image/jpeg','image/JPEG','image/png','image/PNG',]);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened. Record not found.');
        }
    }

    public function connectionData($request){

        $businessObj = new BusinessEntity();
        $businessResult = $businessObj->userSelectedBusiness($request);

        if ($businessResult['_metadata']['outcomeCode'] != 200) {
            return $this->helpError(1, 'Problem in selection of user business.');
        }

        $type = $request->type;
        $businessResult = $businessResult['records'];
        $businessId = $businessResult['business_id'];
        $userId = $businessResult['user_id'];

        Log::info("businessId $businessId ");

        $data =  SocialMediaMaster::where('business_id', $businessId)->first();
        Log::info($data);
            return $this->helpReturn("Successfully.", $data);
        }

        public function facebookWidgetData($request){

            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];

            $data =  SocialMediaMaster::where('business_id', $businessId)->first();

            $graphStatsQueryCurrent = StatTracking::where(['social_media_id' => $data['id'], 'type' => 'LK']);

            $currentMonth = date('m');
            $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
                $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
            });

            $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date')->sum('count');

            $graphStatsQueryLast = StatTracking::where(['social_media_id' => $data['id'], 'type' => 'LK']);

            $lastMonth = date('m', strtotime("-1 month"));
            $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
                $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
            });

            $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date')->sum('count');


            $totalGraphData = StatTracking::where(['social_media_id' => $data['id'], 'type' => 'LK'])->sum('count');

            if ((int)$totalGraphData > 0){
                $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$totalGraphData;
                $total = $total*100;
            }else{
                $total = 0;
            }

            $this->data['pageLikes'] = $data['page_likes_count'];
            $this->data['likesPercent'] = $total;

            return $this->helpReturn("Successfully.", $this->data);
        }
}
