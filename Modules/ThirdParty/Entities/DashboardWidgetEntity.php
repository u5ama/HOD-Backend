<?php

namespace Modules\ThirdParty\Entities;

use App\Entities\AbstractEntity;
use App\Services\SessionService;
use Modules\ThirdParty\Models\SMediaReview;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Modules\ThirdParty\Models\StatTracking;
use Modules\ThirdParty\Models\TripadvisorMaster;
use Modules\ThirdParty\Models\TripadvisorReview;

class DashboardWidgetEntity extends AbstractEntity {

    protected $sessionService;

    public function __construct()
    {
        $this->sessionService = new SessionService();
    }

    public function lastReviews($userData, $numberofreviews = 5)
    {
        $connectionIds = $this->connectionIds($userData);
        $lastReviews = TripadvisorReview::whereIn('third_party_id',$connectionIds)->orderBy('review_date', 'desc')->take($numberofreviews)->get();
        return $lastReviews;
    }
    public function allReviews($userData)
    {
        $connectionIds = $this->connectionIds($userData);

        $allReviews = TripadvisorReview::whereIn('third_party_id',$connectionIds)->orderBy('review_date', 'desc')->paginate(10);
        return $allReviews;
    }
    public function overAllRating($userData)
    {
        # code...
        $connectionIds = $this->connectionIds($userData);

        $overAllRatingGoogle = TripadvisorReview::whereIn('third_party_id',$connectionIds)->get()->average('rating');
        $overAllRatingFacebook = SocialMediaMaster::select('average_rating')->where('business_id',$userData['business_id'])->first();

        if (!empty($overAllRatingFacebook)){
            $total = ($overAllRatingGoogle + $overAllRatingFacebook['average_rating']) / 2;
        }else{
            $total = $overAllRatingGoogle;
        }

        if ($total == null){
            $total = 0;
        }
        return $total;
    }
    public function publicReviews($userData)
    {
        # code...
        $connectionIds = $this->connectionIds($userData);
        $connectionIdsFb = $this->connectionIdsFb($userData);

        $publicReviewsGoogle = TripadvisorReview::whereIn('third_party_id',$connectionIds)->get()->count();
        $publicReviewsFb = SMediaReview::whereIn('social_media_id',$connectionIdsFb)->get()->count();
        $total = $publicReviewsGoogle + $publicReviewsFb;

        return $total;
    }
    public function reviewsGrowth($userData)
    {
        # code...
        $connectionIds = $this->connectionIds($userData);
        $connectionIdsFb = $this->connectionIdsFb($userData);

        /*For GOOGLE*/
        $graphStatsQueryCurrent = StatTracking::where(['user_id' => $userData['id'], 'third_party_id' => $connectionIds, 'type' => 'RV']);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date', 'count')->count();
        /*For Current Month records*/

        /*For previous Month records*/
        $graphStatsQueryLast = StatTracking::where(['user_id' => $userData['id'], 'third_party_id' => $connectionIds, 'type' => 'RV']);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date', 'count')->count();

        $graphStatsQueryTotal = StatTracking::where(['user_id' => $userData['id'], 'third_party_id' => $connectionIds, 'type' => 'RV'])->count();
        /*For previous Month records*/
        /*For GOOGLE*/


        /*Total Calculations*/
        $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$graphStatsQueryTotal;
        $totalPercent = $total*100;

        return $totalPercent;
    }
    public function sentiments($userData)
    {
        # code...
        $connectionIds = $this->connectionIds($userData);

        $rating3_4_5 = TripadvisorReview::whereIn('third_party_id',$connectionIds)->whereIn('rating',[3,4,5])->get()->count();
        if($rating3_4_5 > 0 ){
            $sentiments = $rating3_4_5/$this->publicReviews($userData)*100;
        }else{
            $sentiments = 0;
        }
        return $sentiments;
    }

    /**
     *
     * @param $userData array
     * @return $connectionIds array
    */
    private function connectionIds($userData)
    {
        # code...

        $businessId = $userData['business_id'];
        $connectionIds = TripadvisorMaster::where('business_id', $businessId)
                ->where('page_url', '!=', '')
                ->get()->pluck('third_party_id');
        return $connectionIds;
    }
    /**
     *
     * @param $userData array
     * @return $connectionIds array
    */
    private function connectionIdsFb($userData)
    {
        # code...

        $businessId = $userData['business_id'];
        $connectionIds = SocialMediaMaster::where('business_id', $businessId)
                ->where('page_url', '!=', '')
                ->get()->pluck('id');
        return $connectionIds;
    }

}
