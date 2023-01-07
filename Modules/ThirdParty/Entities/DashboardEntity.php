<?php

namespace Modules\ThirdParty\Entities;

use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\GoogleAdwords\Models\GoogleAdwordsMaster;
use Modules\ThirdParty\Models\StatTracking;
use GuzzleHttp\Client;
use Exception;
use Log;
use JWTAuth;
use DB;
use Modules\Business\Entities\BusinessEntity;
use Modules\CRM\Models\ReviewRequest;
use Modules\CRM\Models\Recipient;
use Modules\GoogleAnalytics\Models\GoogleAnalyticsMaster;
use Modules\ThirdParty\Models\SocialMediaInsight;
use Modules\Task\Models\BusinessTask;
use Modules\ThirdParty\Models\UserIssues;
use Modules\ThirdParty\Models\TripadvisorMaster;
use Modules\Yelp\Models\YelpMaster;
use Modules\GooglePlace\Models\GooglePlaceMaster;

use Modules\ThirdParty\Models\TripadvisorReview;
use Modules\ThirdParty\Entities\TripAdvisorEntity;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\MarketingObjectiveEntity;

use Modules\Business\Models\Business;
use Modules\ThirdParty\Models\SMediaReview;
use Modules\ThirdParty\Models\SocialMediaLike;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Config;

class DashboardEntity extends AbstractEntity
{
    use UserAccess;

    public function thirdPartyReviewsCount($request)
    {
        try {
            $businessObj = new BusinessEntity();
            \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
            $user = JWTAuth::toUser();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user busienss.');
            }
            $businessResult = $businessResult['records'];

            $types = $request->get('type');

            if (!is_array($types)) {
                $types = [
                    [
                        'type' => $types,
                        'is_type' => !empty($request->get('is_type')) ? $request->get('is_type') : 'day',
                    ]
                ];
            }

            $statusData = [];
            $i = 0;
            foreach ($types as $type) {
                $currentType = strtolower($type['type']);
                $reviewsType = strtolower($type['is_type']);
                $thirdPartyResult = [];

                $typeRequested = str_replace('-', ' ', ucfirst($currentType));

                $thirdPartyResult = TripadvisorMaster::where(
                    [
                        'business_id' => $businessResult['business_id'],
                        'type' => $typeRequested
                    ]
                )->first();

                if (!empty($thirdPartyResult['name'])) {
                    if ($currentType == 'google-places') {
                        $dateFormat = 'Y-m-d';
                    } else {
                        $dateFormat = 'Y-m-d';
                    }

                    $currentDate = Carbon::now($user->time_zone);
                    $FormatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format($dateFormat);

                    $weekDate = Carbon::now($user->time_zone)->subDays(7);
                    $formatedWeekDate = Carbon::createFromFormat('Y-m-d H:i:s', $weekDate)->format($dateFormat);

                    if ($reviewsType == 'all') {
                        $count = $thirdPartyResult['average_rating'];
                    } else {
                        $count = TripadvisorReview::where('third_party_id', $thirdPartyResult['third_party_id'])
                            ->where(function ($query) use ($reviewsType, $FormatedCurrentDate, $formatedWeekDate) {
                                if ($reviewsType == 'week') {
                                    $query->where(DB::raw("STR_TO_DATE(`review_date`, '%m-%d-%Y')"), '<=', $FormatedCurrentDate);
                                    $query->where(DB::raw("STR_TO_DATE(`review_date`, '%m-%d-%Y')"), '>=', $formatedWeekDate);
                                } elseif ($reviewsType == 'day') {
                                    $query->where(DB::raw("STR_TO_DATE(`review_date`, '%m-%d-%Y')"), '=', $FormatedCurrentDate);
                                }

                            })
                            ->avg('rating');
                    }
                    $count = round($count, 1);

                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['count'] = $count;

                } else {
                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['message'] = $typeRequested . ' not setup.';
                }
                $i++;
            }

            return $this->helpReturn("Reviews Count Result.", $statusData);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }
    }

    /**
     * Get historical reviews from stat tracking
     * table and this is not needed to be integrated now
     * just for future implementation
     */

    public function getHistoricalReviewsCount($request)
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
            $businessResult = $businessResult['records'];

            $types = $request->get('type');

            if (!is_array($types)) {
                $types = [
                    [
                        'type' => $types,
                        'is_type' => !empty($request->get('is_type')) ? $request->get('is_type') : 'day',
                    ]
                ];
            }

            $statusData = [];
            $i = 0;
            foreach ($types as $type) {
                $currentType = strtolower($type['type']);
                $reviewsType = strtolower($type['is_type']);
                $thirdPartyResult = [];

                $typeRequested = str_replace('-', ' ', ucfirst($currentType));

                $thirdPartyResult = TripadvisorMaster::where(
                    [
                        'business_id' => $businessResult['business_id'],
                        'type' => $typeRequested
                    ]
                )->first();

                if (!empty($thirdPartyResult['name'])) {
                    if ($currentType == 'google-places') {
                        $dateFormat = 'Y-m-d';
                    } else {
                        $dateFormat = 'Y-m-d';
                    }

                    $currentDate = Carbon::now($user->time_zone);
                    $FormatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format($dateFormat);

                    $weekDate = Carbon::now($user->time_zone)->subDays(7);
                    $formatedWeekDate = Carbon::createFromFormat('Y-m-d H:i:s', $weekDate)->format($dateFormat);

                    if ($reviewsType == 'all') {
                        $counts = $thirdPartyResult['review_count'];
                    } else {
                        $counts = StatTracking::where('third_party_id', $thirdPartyResult['third_party_id'])
                            ->where('type', 'RV')
                            ->where('site_type', $currentType)
                            ->where(function ($query) use ($reviewsType, $FormatedCurrentDate, $formatedWeekDate) {
                                if ($reviewsType == 'week') {
                                    $query->where(DB::raw("STR_TO_DATE(`activity_date`, '%m-%d-%Y')"), '<=', $FormatedCurrentDate);
                                    $query->where(DB::raw("STR_TO_DATE(`activity_date`, '%m-%d-%Y')"), '>=', $formatedWeekDate);
                                } elseif ($reviewsType == 'day') {

                                    $query->where(DB::raw("STR_TO_DATE(`activity_date`, '%m-%d-%Y')"), '=', $FormatedCurrentDate);
                                }
                            })
                            ->count();
                    }
                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['count'] = $counts;
                } else {
                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['message'] = $typeRequested . ' not setup.';
                }
                $i++;
            }

            return $this->helpReturn("Historical Reviews Count Result.", $statusData);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }

    /**
     * Get historical rating count from stat tracking
     * table this is not needed to be integrated now
     * just for future implementation
     */

    public function getHistoricalRatingCount($request)
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
            $businessResult = $businessResult['records'];

            $types = $request->get('type');

            if (!is_array($types)) {
                $types = [
                    [
                        'type' => $types,
                        'is_type' => !empty($request->get('is_type')) ? $request->get('is_type') : 'day',
                    ]
                ];
            }

            $statusData = [];
            $i = 0;
            foreach ($types as $type) {
                $currentType = strtolower($type['type']);
                $reviewsType = strtolower($type['is_type']);
                $thirdPartyResult = [];

                $typeRequested = str_replace('-', ' ', ucfirst($currentType));

                $thirdPartyResult = TripadvisorMaster::where(
                    [
                        'business_id' => $businessResult['business_id'],
                        'type' => $typeRequested
                    ]
                )->first();

                if (!empty($thirdPartyResult['name'])) {
                    if ($currentType == 'google-places') {
                        $dateFormat = 'Y-m-d';
                    } else {
                        $dateFormat = 'Y-m-d';
                    }

                    $currentDate = Carbon::now($user->time_zone);
                    $FormatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format($dateFormat);
                    $weekDate = Carbon::now($user->time_zone)->subDays(7);
                    $formatedWeekDate = Carbon::createFromFormat('Y-m-d H:i:s', $weekDate)->format($dateFormat);

                    if ($reviewsType == 'all') {
                        $avgRating = $thirdPartyResult['average_rating'];
                    } else {
                        $avgRating = StatTracking::where('third_party_id', $thirdPartyResult['third_party_id'])
                            ->where('type', 'RG')
                            ->where('site_type', $request->type)
                            ->where(function ($query) use ($reviewsType, $FormatedCurrentDate, $formatedWeekDate) {
                                if ($reviewsType == 'week') {
                                    $query->where(DB::raw("STR_TO_DATE(`activity_date`, '%m-%d-%Y')"), '<=', $FormatedCurrentDate);
                                    $query->where(DB::raw("STR_TO_DATE(`activity_date`, '%m-%d-%Y')"), '>=', $formatedWeekDate);
                                } elseif ($reviewsType == 'day') {

                                    $query->where(DB::raw("STR_TO_DATE(`activity_date`, '%m-%d-%Y')"), '=', $FormatedCurrentDate);
                                }

                            })->avg('count');
                        $avgRating = round($avgRating, 1);
                    }

                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['count'] = $avgRating;
                } else {
                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['message'] = $typeRequested . ' not setup.';
                }
                $i++;
            }

            return $this->helpReturn("Historical Rating Count Result.", $statusData);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }

    }

    /**
     * get counts from third_party_review and
     * save data in stat_tracking table
     */
    public function countHistoricalData($request)
    {
        try {
            if (isset($request->business_id) && $request->get('token') == '') {  //cron job section

                $businessId = $request->business_id;
                $currentDate = Carbon::now();
            }
            else {    //token based user get and store record in state tracking
                $businessObj = new BusinessEntity();
                \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
                $user = JWTAuth::toUser();
                $businessResult = $businessObj->userSelectedBusiness($request);
                $businessId = $businessResult['records']['business_id'];

                if ($businessResult['_metadata']['outcomeCode'] != 200) {
                    return $this->helpError(1, 'Problem in selection of user business.');
                }
                $currentDate = Carbon::now($user->time_zone);
            }

            $thirdPartytypes = moduleSiteList();

            $dateFormat = dateFormatUsing();
            $FormatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format($dateFormat);

            $thirdPartyIds = TripadvisorMaster::select('third_party_id')->where('business_id', $businessId)->get()->toArray();

            if (isset($request->business_id) && $request->get('token') == '') {
                Log::info('data cron job');
                $historical_reviews = TripadvisorReview::whereIn('third_party_id', $thirdPartyIds)//Query for historical reivew
                //->where('review_id','>=',$firstReviewId)
                ->whereIn('type', $thirdPartytypes)
                    ->where(DB::raw("STR_TO_DATE(`review_date`, '%Y-%m-%d')"), '<=', $FormatedCurrentDate)
                    ->select('third_party_id', 'review_date', 'type', DB::raw('count(review_date) as total'))
                    ->groupBy('type', 'review_date', 'third_party_id')
                    ->get()->toArray();
            } else {
                $historical_reviews = TripadvisorReview::whereIn('third_party_id', $thirdPartyIds)//Query for historical reivew
                ->where('type', $request['type'])
                    ->where(DB::raw("STR_TO_DATE(`review_date`, '%Y-%m-%d')"), '<=', $FormatedCurrentDate)
                    ->select('third_party_id', 'review_date', 'type', DB::raw('count(review_date) as total'))
                    ->groupBy('type', 'review_date', 'third_party_id')
                    ->get()->toArray();
            }
            /*********************************Review section **********************/

            if (empty($historical_reviews)) {
                if ($request->business_id) {
                    Log::info('No Review Found.'); //use for cron job
                } else {
                    return $this->helpReturn("No Review Found."); //return use for token based call
                }
            }

            foreach ($historical_reviews as $review) {
                $reviewDate = getFormattedDate($review['review_date']);
                $appendReviewArray[] = [
                    'third_party_id' => $review['third_party_id'],
                    'activity_date' => $reviewDate,
                    'site_type' => $review['type'],
                    'count' => $review['total'],
                    'type' => 'RV',
                    'user_id' => $user['id'],
                ];
            }

            /**************************Rating Section***************************/
            if (isset($request->business_id) && $request->get('token') == '') {

                $historical_rating = TripadvisorReview::whereIn('third_party_id', $thirdPartyIds)//Query for Rating
                ->whereIn('type', $thirdPartytypes)
                    ->where(DB::raw("STR_TO_DATE(`review_date`, '%Y-%m-%d')"), '<=', $FormatedCurrentDate)
                    ->select('third_party_id', 'review_date', 'type', 'review_date', DB::raw('sum(rating) as sum'), DB::raw('count(review_date) as total'))
                    ->groupBy('review_date', 'third_party_id', 'review_date', 'type')->get()->toArray();
            } else {
                $historical_rating = TripadvisorReview::whereIn('third_party_id', $thirdPartyIds)//Query for Rating
                ->where('type', $request['type'])
                    ->where(DB::raw("STR_TO_DATE(`review_date`, '%Y-%m-%d')"), '<=', $FormatedCurrentDate)
                    ->select('third_party_id', 'review_date', 'type', 'review_date', DB::raw('sum(rating) as sum'), DB::raw('count(review_date) as total'))
                    ->groupBy('review_date', 'third_party_id', 'review_date', 'type')->get()->toArray();

            }

            if (empty($historical_rating)) {
                if ($request->business_id) {
                    Log::info('No Rating Found.'); //use for cron job
                } else {
                    return $this->helpReturn("No Rating Found."); //return use for token based call
                }

            }
            foreach ($historical_rating as $rating) { //loop for facebook rating
                $reviewDate = getFormattedDate($rating['review_date']);

                $appendRatingArray[] = [
                    'third_party_id' => $rating['third_party_id'],
                    'activity_date' => $reviewDate,
                    'site_type' => $rating['type'],
                    'count' => $rating['sum'] / $rating['total'], //sun amd total find using query and submit in database
                    'type' => 'RG',
                    'user_id' => $user['id'],
                ];
            }

            if (isset($businessId)) {
                StatTracking::whereIn('third_party_id', $thirdPartyIds)->delete();
                StatTracking::insert($appendReviewArray); //for  Review
                StatTracking::insert($appendRatingArray); //for  Rating
            } else {
                bulk_insert("stat_tracking", $appendReviewArray);
                bulk_insert("stat_tracking", $appendRatingArray);
            }
            $finalArray[] = [
                'reviews' => $appendReviewArray,
                'ratings' => $appendRatingArray,
            ];

            return $this->helpReturn("Reviews Update.", $finalArray);

        } catch (Exception $e) {
            Log::info(" countHistoricalData > " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }
    }



    public function getGraphStatsCount($request)
    {
        try {
            $businessObj = new BusinessEntity();
            \Tymon\JWTAuth\Facades\JWTAuth::setToken($request->input('token'));
            $user = JWTAuth::toUser();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $businessResult = $businessResult['records'];

            $types = $request->get('type');

            if (!is_array($types)) {
                $types = [
                    [
                        'type' => $types,
                        'is_type' => !empty($request->get('is_type')) ? $request->get('is_type') : 'day',
                        'category_type' => !empty($request->get('category_type')) ? $request->get('category_type') : 'day',
                    ]
                ];
            }

            $statusData = [];
            $i = 0;
           // $objectiveManager = new MarketingObjectiveEntity();
            $objective = '';
            $categoryHeading = '';

            foreach ($types as $type) {
                $currentType = strtolower($type['type']);
                $reviewsType = strtolower($type['is_type']);
                $category_type = $request->get('category_type');

                if (strtoupper($category_type) == 'RV') {
                    $categoryHeading = 'Reviews';
                } elseif (strtoupper($category_type) == 'LK') {
                    $categoryHeading = 'Likes';
                } elseif (strtoupper($category_type) == 'RG') {
                    $categoryHeading = 'Rating';
                } elseif (strtoupper($category_type) == 'PV') {
                    $categoryHeading = 'Page View';
                } elseif (strtoupper($category_type) == 'AS') {
                    $categoryHeading = 'Ad Spend';
                } elseif (strtoupper($category_type) == 'CC') {
                    $categoryHeading = 'Cost per conversion';
                } elseif (strtoupper($category_type) == 'AC') {
                    $categoryHeading = 'revenue';
                }

                $typeRequested = str_replace('-', ' ', ucfirst($currentType));

                if ($typeRequested == 'Facebook') {
                    $thirdPartyResult = SocialMediaMaster::where(
                        [
                            'business_id' => $businessResult['business_id'],
                            'type' => $typeRequested
                        ]
                    )->first();
                } elseif ($typeRequested == 'Googleanalytics' || $typeRequested == 'Google analytics') {
                    if ($typeRequested == 'Google analytics') {
                        $typeRequested = 'Googleanalytics';
                    }

                   $thirdPartyResult = GoogleAnalyticsMaster::where(
                        [
                            'business_id' => $businessResult['business_id'],
                        ]
                    )->first();
                }elseif ($typeRequested == 'Googleadwords' || $typeRequested == 'Google adwords') {
                    if ($typeRequested == 'Google adwords') {
                        $typeRequested = 'Googleadwords';
                    }

                   $thirdPartyResult = GoogleAdwordsMaster::where(
                        [
                            'business_id' => $businessResult['business_id'],
                        ]
                    )->first();
                } else {
                    $thirdPartyResult = TripadvisorMaster::where(
                        [
                            'business_id' => $businessResult['business_id'],
                            'type' => $typeRequested
                        ]
                    )->first();
                }

                if (!empty($thirdPartyResult['name'])) {
                    $dateFormat = dateFormatUsing();
                    $currentDate = Carbon::now($user->time_zone);
                    $yesterdayDate = Carbon::yesterday($user->time_zone);

                    $formattedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format($dateFormat);
                    $formattedYesterdayDate = Carbon::createFromFormat('Y-m-d H:i:s', $yesterdayDate)->format($dateFormat);
                    $formattedWeekDate = '';

                    if ($reviewsType == 'week') {
                        $weekDate = Carbon::now($user->time_zone)->subDays(6);
                        $lastWeekDate = Carbon::now($user->time_zone)->subDays(13);

                        $formattedWeekDate = Carbon::createFromFormat('Y-m-d H:i:s', $weekDate)->format($dateFormat);
                        $formattedLastWeekDate = Carbon::createFromFormat('Y-m-d H:i:s', $lastWeekDate)->format($dateFormat);

                        $weekDates = extractWeekDays($formattedWeekDate);
                        $lastWeekDates = extractWeekDays($formattedLastWeekDate);
                    }

                    $graphStatsQuery = StatTracking::where(function ($q) use ($typeRequested, $thirdPartyResult) {
                        if ($typeRequested == 'Facebook') {
                            $q->where('social_media_id', $thirdPartyResult['id']);
                        } else if ($typeRequested == 'Googleanalytics') {
                            $q->where('google_analytics_id', $thirdPartyResult['id']);
                        }else if ($typeRequested == 'Googleadwords') {
                            $q->where('google_adwords_id', $thirdPartyResult['id']);
                        } else {
                            $q->where('third_party_id', $thirdPartyResult['third_party_id']);
                        }
                    })->where('type', $category_type)->where('site_type', $typeRequested);

                    $graphStatsSelection = '';
                    if ($reviewsType == 'week' || $reviewsType == 'day') {
                        $graphStatsSelection = clone $graphStatsQuery;
                    }

                    $graphStatsQuery->where(function ($query) use ($reviewsType, $formattedCurrentDate, $formattedWeekDate) {
                        if ($reviewsType == 'week') {
                            $query->where('activity_date', '<=', $formattedCurrentDate);
                            $query->where('activity_date', '>=', $formattedWeekDate);
                        } elseif ($reviewsType == 'day') {
                            $query->where('activity_date', '=', $formattedCurrentDate);
                        }
                    });

                    $graphStats = $graphStatsQuery->select('activity_date', 'count')->get()->toArray();

                    /*New query for all Data*/
                    if ($reviewsType == 'all'){
                        $last_twelve_month_ary = $salesTotalAry = [];
                        $varCounter = 0;
                        for ($g = 11; $g > -1; $g--){
                            $varCounter++;
                            $bar_year = date("Y", strtotime("-$g months"));
                            $bar_month = date("m", strtotime("-$g months"));
                            //$last_twelve_month_ary[] = '"'.date("M Y", strtotime("-$g months")).'"';

                            ${'standard_query_'.$varCounter} = clone $graphStatsQuery;
                            //prepare condition
                            $data = ${'standard_query_'.$varCounter}->whereMonth('activity_date', '=', $bar_month)->whereYear('activity_date', '=', $bar_year)->sum('count') ;
                            $last_twelve_month_ary[date("M Y", strtotime("-$g months"))] = $data;
                            if ($category_type == 'RG'){
                                $data = ${'standard_query_'.$varCounter}->whereMonth('activity_date', '=', $bar_month)->whereYear('activity_date', '=', $bar_year)->avg('count') ;
                                $last_twelve_month_ary[date("M Y", strtotime("-$g months"))] = $data;
                            }
                        }

                        $encodedData = [];
                        $k= 0;
                        foreach($last_twelve_month_ary as $index => $val)
                        {
                            $encodedData[$k]['activity_date'] = $index;
                            $encodedData[$k]['count'] = $val;

                            $k++;
                        }
                        $graphStats = $encodedData;
                    }
                    /*New query for all Data*/

                    $insightData = [];

                    /**
                     * review request must not be all and widget category type is not be Reviews, likes..
                     */
                    if (!($reviewsType == 'all' && ($category_type == 'RV' || $category_type == 'LK'))) {
                       // $objectiveData = $objectiveManager->getObjectiveQuery($currentType, $categoryHeading);

                        if (!empty($objectiveData)) {
                            $objective = $objectiveData['id'];
                        }
                    }

                    if ($reviewsType == 'all' && $category_type == 'RV') {
                        if ($typeRequested == 'Facebook') {
                            $counts = $thirdPartyResult['page_reviews_count'];
                        } else {
                            $counts = $thirdPartyResult['review_count'];
                        }
                    } elseif ($reviewsType == 'all' && $category_type == 'LK') {
                        $counts = $thirdPartyResult['page_likes_count'];
                    } elseif ($reviewsType == 'all' && $category_type == 'RG') {
                        $counts = $thirdPartyResult['average_rating'];
                        $insightData = insightTitle($counts, '', '', $category_type, $objective);
                    } elseif ($category_type == 'RG') {
                        $counts = $graphStatsQuery->avg('count');
                        $counts = round($counts, 1);
                        $insightData = insightTitle($counts, '', '', $category_type, $objective);
                    }elseif ($category_type == 'AS') {
                        $counts = $graphStatsQuery->count('count');
                        $counts = round($counts, 1);
                        $insightData = insightTitle($counts, '', '', $category_type, $objective);
                    }elseif ($category_type == 'CC') {
                        $counts = $graphStatsQuery->count('count');
                        $counts = round($counts, 1);
                        $insightData = insightTitle($counts, '', '', $category_type, $objective);
                    }elseif ($category_type == 'AC') {
                        $counts = $graphStatsQuery->count('count');
                        $counts = round($counts, 1);
                        $insightData = insightTitle($counts, '', '', $category_type, $objective);
                    } elseif ($category_type == 'PV') {
                        $counts = $graphStatsQuery->sum('count');
                        $lastCounts = 0;

                        if ($reviewsType == 'day') {
                            $graphStatsSelection->where(function ($query) use ($formattedYesterdayDate) {
                                $query->where('activity_date', '=', $formattedYesterdayDate);
                            });

                            if (!empty($graphStatsSelection)) {
                                $graphStatsSelection->select('activity_date', 'count')->orderBy('activity_date', 'ASC')->get()->toArray();
                            }

                            $lastCounts = $graphStatsSelection->sum('count');
                        }

                        $insightData = insightTitle($counts, $lastCounts, $reviewsType, $category_type, $objective);
                    } elseif ($category_type == 'RV' || $category_type == 'LK') {
                        $counts = $graphStatsQuery->sum('count');

                        // comparison of week with last week
                        /****************** Part to cover Last week & today-yesterday case ******************************/
                        if ($reviewsType == 'week') {
                            $graphStatsSelection->where(function ($query) use ($formattedLastWeekDate, $formattedWeekDate) {
                                $query->where('activity_date', '<', $formattedWeekDate);
                                $query->where('activity_date', '>=', $formattedLastWeekDate);
                            });
                        } elseif ($reviewsType == 'day') {
                            $graphStatsSelection->where(function ($query) use ($formattedYesterdayDate) {
                                $query->where('activity_date', '=', $formattedYesterdayDate);
                            });
                        }

                        if (!empty($graphStatsSelection)) {
                            $graphStatsSelection->select('activity_date', 'count')->orderBy('activity_date', 'ASC')->get()->toArray();
                        }

                        $lastCounts = $graphStatsSelection->sum('count');
                        $insightData = insightTitle($counts, $lastCounts, $reviewsType, $category_type, $objective);

                        /****************** Part to cover Last week & today-yesterday case ******************************/
                    }

                    if ($reviewsType == 'week') {
                        if (!empty($graphStats)) {
                            foreach ($graphStats as $row) {
                                $activityDate = $row['activity_date'];
                                $key = array_search($activityDate, array_column($weekDates, 'activity_date'));

                                if (isset($weekDates[$key]['activity_date']) && $activityDate == $weekDates[$key]['activity_date']) {
                                    $weekDates[$key]['count'] = $row['count'];
                                }
                            }
                        }

                        $graphStats = $weekDates;
                    } else {
                        // if graph stats is empty then show data with current date 0
                        if (empty($graphStats)) {
                            $graphStats[0]['activity_date'] = $formattedCurrentDate;
                            $graphStats[0]['count'] = 0;
                        }
                    }

                    if (empty($insightData)) {
                        $insightData = [
                            'objective' => '',
                            'insightTitle' => '',
                            'insightDescription' => '',
                            'insightStatus' => '',
                        ];
                    }
                    $statusData[$i] = $insightData;
                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['name'] = $thirdPartyResult['name'];

                    if (!empty($thirdPartyResult['website'])) {
                        $statusData[$i]['website'] = $thirdPartyResult['website'];
                    }

                    if ($typeRequested == 'Googleanalytics') {
                        $statusData[$i]['typeTitle'] = 'Google analytics';
                    }
                    else if ($typeRequested == 'Googleadwords') {
                        $statusData[$i]['typeTitle'] = 'Google adwords';
                    } else {
                        $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    }
                    $statusData[$i]['count'] = $counts;
                    $statusData[$i]['graph_data'] = $graphStats;
                } else {
                    $statusData[$i]['type'] = $currentType;
                    $statusData[$i]['typeTitle'] = ucwords(strtolower($typeRequested));
                    $statusData[$i]['message'] = ucwords(strtolower($typeRequested)) . ' not connected';
                }
                $i++;
            }
            Log::info("Ready");
          //  Log::info($statusData);
            return $this->helpReturn("Dashboard Widget and Graph Stats.", $statusData);
        } catch (Exception $e) {
            Log::info("getGraphStatsCount " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened.');
        }
    }
}
